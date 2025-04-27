<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DianService;

class ProcessDianInvoices extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'dian:process-invoices 
                            {--send : Envía facturas pendientes a DIAN}
                            {--check : Verifica el estado de facturas pendientes en DIAN}
                            {--test : Usa el entorno de pruebas de DIAN}';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Procesa las facturas pendientes de envío o verificación con DIAN';

    /**
     * El servicio de DIAN.
     *
     * @var \App\Services\DianService
     */
    protected $dianService;

    /**
     * Crear una nueva instancia del comando.
     *
     * @param \App\Services\DianService $dianService
     * @return void
     */
    public function __construct(DianService $dianService)
    {
        parent::__construct();
        $this->dianService = $dianService;
    }

    /**
     * Ejecutar el comando de consola.
     *
     * @return int
     */
    public function handle()
    {
        $testMode = $this->option('test');
        
        if ($testMode) {
            $this->info('Usando el entorno de pruebas de DIAN');
        }
        
        // Si no se especifica ninguna opción, realizar ambas operaciones
        $shouldSend = $this->option('send') || (!$this->option('send') && !$this->option('check'));
        $shouldCheck = $this->option('check') || (!$this->option('send') && !$this->option('check'));
        
        if ($shouldSend) {
            $this->info('Procesando facturas pendientes de envío a DIAN...');
            $results = $this->dianService->processPendingInvoices($testMode);
            
            $this->table(
                ['Total', 'Exitosas', 'Fallidas'],
                [[$results['total'], $results['success'], $results['failed']]]
            );
        }
        
        if ($shouldCheck) {
            $this->info('Actualizando estado de facturas pendientes en DIAN...');
            $results = $this->dianService->updatePendingInvoicesStatus($testMode);
            
            $this->table(
                ['Total', 'Exitosas', 'Fallidas'],
                [[$results['total'], $results['success'], $results['failed']]]
            );
        }
        
        return Command::SUCCESS;
    }
}
