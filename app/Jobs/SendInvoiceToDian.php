<?php

namespace App\Jobs;

use App\Events\InvoiceAccepted;
use App\Models\Invoice;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendInvoiceToDian implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Array con los tiempos de espera (en minutos) para reintentos.
     */
    protected array $backoffTimes = [5, 10, 30, 60, 120];

    /**
     * El número de intentos para el trabajo.
     *
     * @var int
     */
    public $tries = 6; // 1 intento inicial + 5 reintentos

    /**
     * Determine el número de segundos para esperar antes de reintentar el trabajo.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return array_map(fn ($minutes) => $minutes * 60, $this->backoffTimes);
    }

    /**
     * El identificador único del trabajo.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'invoice_' . $this->invoice->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->invoice->signed_xml_path) {
            Log::error('No se puede enviar la factura a DIAN porque no tiene XML firmado', [
                'invoice_id' => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number
            ]);
            return;
        }

        Log::info('Iniciando envío de factura a DIAN', [
            'invoice_id' => $this->invoice->id, 
            'retry_count' => $this->invoice->dian_retry_count
        ]);

        try {
            // Marcar como enviada
            $this->invoice->update([
                'dian_status' => 'SENT',
                'dian_sent_at' => now(),
            ]);

            // Obtener el contenido del XML firmado
            $xmlContent = Storage::get($this->invoice->signed_xml_path);
            if (!$xmlContent) {
                throw new \Exception('No se pudo leer el archivo XML firmado');
            }

            // Mock de la llamada a la API de la DIAN (simulación)
            $response = $this->mockDianApiCall($xmlContent);

            // Procesar la respuesta
            $this->processResponse($response);

        } catch (\Exception $e) {
            Log::error('Error al enviar factura a DIAN', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
                'retry_count' => $this->invoice->dian_retry_count
            ]);

            // Actualizar el estado de la factura a REJECTED para posteriores reintentos
            $this->invoice->update([
                'dian_status' => 'REJECTED',
                'dian_response_code' => 'ERROR',
                'dian_response_message' => $e->getMessage(),
                'dian_processed_at' => now(),
            ]);

            // Si hemos alcanzado el máximo de reintentos, fallar definitivamente
            if ($this->invoice->dian_retry_count >= count($this->backoffTimes)) {
                Log::warning('Se alcanzó el máximo de reintentos para enviar la factura a DIAN', [
                    'invoice_id' => $this->invoice->id,
                    'retry_count' => $this->invoice->dian_retry_count
                ]);
                $this->fail($e);
                return;
            }

            // Incrementar el contador de reintentos
            $this->invoice->increment('dian_retry_count');
            
            // Lanzar la excepción para que Laravel reintente el trabajo
            throw $e;
        }
    }

    /**
     * Simulación de llamada a la API de la DIAN (entorno de habilitación)
     * 
     * @param string $xmlContent El contenido del XML firmado
     * @return array La respuesta simulada
     */
    private function mockDianApiCall(string $xmlContent): array
    {
        // En producción, aquí usaríamos Guzzle para hacer la petición real a la DIAN
        // $client = new Client();
        // $response = $client->post('https://habilitacion.dian.gov.co/api/validacion', [
        //     'headers' => [
        //         'Content-Type' => 'application/xml',
        //         'Accept' => 'application/json',
        //     ],
        //     'body' => $xmlContent,
        // ]);
        
        // Simulamos una respuesta
        // Para probar el retry, podemos alterar esto para que falle según alguna condición
        $shouldFail = $this->invoice->dian_retry_count < 2 && rand(0, 1) > 0;
        
        if ($shouldFail) {
            throw new \Exception('Error de conexión con DIAN (simulado)');
        }

        // Simulamos una respuesta exitosa
        return [
            'status' => 'success',
            'code' => 'ACCEPTED',
            'message' => 'Documento electrónico validado correctamente',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Procesa la respuesta de la DIAN
     * 
     * @param array $response La respuesta de la API
     * @return void
     */
    private function processResponse(array $response): void
    {
        if ($response['status'] === 'success' && $response['code'] === 'ACCEPTED') {
            // Actualizar el estado de la factura a aceptada
            $this->invoice->update([
                'dian_status' => 'ACCEPTED',
                'dian_response_code' => $response['code'],
                'dian_response_message' => $response['message'],
                'dian_processed_at' => now(),
            ]);

            Log::info('Factura aceptada por DIAN', [
                'invoice_id' => $this->invoice->id,
                'response' => $response
            ]);

            // Disparar evento de factura aceptada
            event(new InvoiceAccepted($this->invoice));
        } else {
            // Actualizar el estado de la factura a rechazada
            $this->invoice->update([
                'dian_status' => 'REJECTED',
                'dian_response_code' => $response['code'] ?? 'UNKNOWN',
                'dian_response_message' => $response['message'] ?? 'Error desconocido',
                'dian_processed_at' => now(),
            ]);

            Log::warning('Factura rechazada por DIAN', [
                'invoice_id' => $this->invoice->id,
                'response' => $response
            ]);

            // Incrementar el contador de reintentos
            $this->invoice->increment('dian_retry_count');
            
            if ($this->invoice->dian_retry_count >= count($this->backoffTimes)) {
                Log::warning('Se alcanzó el máximo de reintentos para la factura', [
                    'invoice_id' => $this->invoice->id
                ]);
                return;
            }
            
            // Lanzar una excepción para que se reintente el trabajo
            $errorMessage = $response['message'] ?? 'Error desconocido';
            throw new \Exception("Error en validación DIAN: {$errorMessage}");
        }
    }
}
