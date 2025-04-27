<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Ejecutar verificación mensual de documentos DIAN expirados
        $schedule->command('dian:cleanup --notify=admin@example.com --dry-run')
            ->monthlyOn(1, '02:00') // Ejecutar el día 1 de cada mes a las 2am
            ->appendOutputTo(storage_path('logs/dian-cleanup.log'));
        
        // Procesar facturas pendientes con DIAN cada hora
        $schedule->command('dian:process-invoices')->hourly();
            
        // Procesar trabajos de la cola
        $schedule->command('queue:work --stop-when-empty --tries=3')
            ->everyMinute()
            ->withoutOverlapping();
            
        // Reintentar trabajos fallidos cada 15 minutos
        $schedule->command('queue:retry-failed --queue=default')
            ->everyFifteenMinutes();
            
        // Limpiar trabajos fallidos mayores a una semana
        $schedule->command('queue:prune-failed --hours=168')
            ->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
    
    /**
     * Los comandos de la aplicación.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\DianCleanupCommand::class,
        \App\Console\Commands\ProcessDianInvoices::class,
    ];
}
