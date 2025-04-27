<?php

namespace App\Console\Commands;

use App\Services\DianStorageService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DianCleanupCommand extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'dian:cleanup {--force : Forzar eliminación sin confirmación} {--notify= : Email para notificar antes de eliminar} {--dry-run : Solo mostrar archivos a eliminar sin ejecutar eliminación}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Verifica y limpia documentos electrónicos con más de 5 años de antigüedad según normativa DIAN';

    /**
     * Servicio para gestionar almacenamiento DIAN
     * 
     * @var DianStorageService
     */
    protected $dianStorageService;

    /**
     * Constructor del comando
     * 
     * @param DianStorageService $dianStorageService
     * @return void
     */
    public function __construct(DianStorageService $dianStorageService)
    {
        parent::__construct();
        $this->dianStorageService = $dianStorageService;
    }

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        $this->info('Verificando documentos electrónicos con antigüedad mayor a 5 años...');
        
        // Obtener documentos expirados
        $expiredDocuments = $this->dianStorageService->getExpiredDocuments();
        
        if (empty($expiredDocuments)) {
            $this->info('No se encontraron documentos que cumplan con el criterio de expiración (5 años).');
            return 0;
        }
        
        $this->info(sprintf('Se encontraron %d documentos con antigüedad mayor a 5 años.', count($expiredDocuments)));
        
        // Agrupar documentos por NIT de comercio
        $documentsBycompany = [];
        foreach ($expiredDocuments as $document) {
            $companyNit = $document['companyNit'];
            if (!isset($documentsBycompany[$companyNit])) {
                $documentsBycompany[$companyNit] = [];
            }
            $documentsBycompany[$companyNit][] = $document;
        }
        
        // Mostrar resumen por comercio
        foreach ($documentsBycompany as $companyNit => $documents) {
            $this->info(sprintf('Comercio NIT %s: %d documentos', $companyNit, count($documents)));
        }
        
        // Notificar antes de eliminar
        $notifyEmail = $this->option('notify');
        if ($notifyEmail) {
            $this->notifyExpiredDocuments($notifyEmail, $documentsBycompany);
            $this->info(sprintf('Se ha notificado al correo %s sobre los documentos a eliminar.', $notifyEmail));
        }
        
        // Si es dry-run, terminar aquí
        if ($this->option('dry-run')) {
            $this->info('Ejecutado en modo dry-run. No se eliminaron documentos.');
            return 0;
        }
        
        // Solicitar confirmación a menos que se use --force
        if (!$this->option('force') && !$this->confirm('¿Está seguro de que desea eliminar estos documentos?')) {
            $this->info('Operación cancelada por el usuario.');
            return 0;
        }
        
        // Proceder con la eliminación
        $deletedCount = 0;
        $errorCount = 0;
        
        foreach ($expiredDocuments as $document) {
            try {
                if (Storage::exists($document['path'])) {
                    Storage::delete($document['path']);
                    $deletedCount++;
                    
                    // Registrar en log
                    Log::info(sprintf(
                        'Documento eliminado por antigüedad: %s (NIT: %s, Año: %s, Mes: %s)',
                        $document['path'],
                        $document['companyNit'],
                        $document['year'],
                        $document['month']
                    ));
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error(sprintf('Error al eliminar %s: %s', $document['path'], $e->getMessage()));
                Log::error(sprintf('Error al eliminar documento %s: %s', $document['path'], $e->getMessage()));
            }
        }
        
        $this->info(sprintf('Proceso completado. Documentos eliminados: %d. Errores: %d.', $deletedCount, $errorCount));
        
        return 0;
    }
    
    /**
     * Notifica por correo sobre los documentos a eliminar
     * 
     * @param string $email Correo electrónico destino
     * @param array $documentsBycompany Documentos agrupados por comercio
     * @return void
     */
    private function notifyExpiredDocuments(string $email, array $documentsBycompany): void
    {
        // En un entorno real, aquí se enviaría un correo usando una clase de Mailable
        // Para fines de demostración, lo simulamos con un log
        
        $summary = [];
        foreach ($documentsBycompany as $companyNit => $documents) {
            $summary[] = sprintf('- NIT %s: %d documentos', $companyNit, count($documents));
        }
        
        $message = sprintf(
            "ALERTA: Documentos electrónicos a eliminar por antigüedad\n\n" .
            "Se detectaron documentos con antigüedad superior a 5 años que serán eliminados según normativa DIAN.\n\n" .
            "Resumen:\n%s\n\n" .
            "Esta operación se ejecutará en las próximas 24 horas. Si desea conservar estos documentos, " .
            "por favor realice una copia de seguridad.\n\n" .
            "Este es un mensaje automático, por favor no responda.",
            implode("\n", $summary)
        );
        
        Log::info('Enviando notificación a ' . $email . ' sobre eliminación de documentos');
        Log::info('Contenido del mensaje: ' . $message);
        
        // En un entorno real:
        // Mail::raw($message, function ($m) use ($email) {
        //     $m->to($email)
        //       ->subject('ALERTA: Eliminación programada de documentos electrónicos');
        // });
    }
} 