<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'invoice_number',
        'type',
        'document_type',
        'cufe',
        'customer_id',
        'customer_name',
        'customer_email',
        'subtotal',
        'tax',
        'total',
        'xml_path',
        'pdf_path',
        'signed_xml_path',
        'issued_at',
        'due_date',
        'notes',
        'access_token',
        'dian_sent_at',
        'dian_processed_at',
        'dian_status',
        'dian_response_code',
        'dian_response_message',
        'dian_retry_count',
        'locally_stored',
        'stored_at',
    ];

    /**
     * Los atributos que deben convertirse a tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'dian_sent_at' => 'datetime',
        'dian_processed_at' => 'datetime',
        'dian_retry_count' => 'integer',
        'locally_stored' => 'boolean',
        'stored_at' => 'datetime',
    ];

    /**
     * Obtiene la empresa a la que pertenece esta factura.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Determina si el total de la factura requiere documento formal.
     *
     * @param float $total
     * @return string
     */
    public static function determineDocumentType(float $total): string
    {
        return $total >= 212000 ? 'invoice' : 'ticket_pos';
    }
    
    /**
     * Genera un token de acceso único para esta factura
     *
     * @return string
     */
    public function generateAccessToken(): string
    {
        $token = Str::random(64);
        $this->update(['access_token' => $token]);
        return $token;
    }

    /**
     * Retorna la URL pública para acceder a esta factura con el token
     *
     * @return string|null
     */
    public function getPublicUrl(): ?string
    {
        if (!$this->access_token) {
            return null;
        }
        
        return url("/facturas/{$this->access_token}");
    }

    /**
     * Retorna la URL pública para descargar el PDF de esta factura con el token
     *
     * @return string|null
     */
    public function getPublicPdfUrl(): ?string
    {
        if (!$this->access_token) {
            return null;
        }
        
        return url("/facturas/{$this->access_token}/pdf");
    }

    /**
     * Obtiene la resolución DIAN asociada a esta factura.
     * 
     * @deprecated Ahora la información de DIAN está en la misma tabla de facturas
     */
    public function dianResolution(): HasOne
    {
        return $this->hasOne(DianResolution::class, 'invoice_id');
    }
    
    /**
     * Obtiene los detalles/líneas de esta factura.
     */
    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }
    
    /**
     * Verifica si la factura ha sido enviada a DIAN.
     *
     * @return bool
     */
    public function isSentToDian(): bool
    {
        return $this->dianResolution && $this->dianResolution->isSent();
    }
    
    /**
     * Verifica si la factura ha sido procesada por DIAN.
     *
     * @return bool
     */
    public function isProcessedByDian(): bool
    {
        return $this->dianResolution && $this->dianResolution->isProcessed();
    }
    
    /**
     * Verifica si la factura ha sido aceptada por DIAN.
     *
     * @return bool
     */
    public function isAcceptedByDian(): bool
    {
        return $this->dianResolution && $this->dianResolution->isAccepted();
    }
    
    /**
     * Verifica si la factura ha sido rechazada por DIAN.
     *
     * @return bool
     */
    public function isRejectedByDian(): bool
    {
        return $this->dianResolution && $this->dianResolution->isRejected();
    }
    
    /**
     * Verifica si la factura está pendiente de envío a DIAN o de respuesta.
     *
     * @return bool
     */
    public function isPendingDian(): bool
    {
        return !$this->dianResolution || $this->dianResolution->isPending();
    }
    
    /**
     * Incrementa el contador de reintentos de envío a DIAN.
     *
     * @return int El nuevo valor del contador de reintentos
     */
    public function incrementDianRetryCount(): int
    {
        if (!$this->dianResolution) {
            $this->dianResolution()->create([
                'company_id' => $this->company_id,
                'dian_retry_count' => 1,
                'dian_status' => 'PENDING'
            ]);
            return 1;
        }
        
        $this->dianResolution->incrementRetryCount();
        return $this->dianResolution->dian_retry_count;
    }
    
    /**
     * Establece el estado de envío a DIAN.
     *
     * @return void
     */
    public function markAsSentToDian(): void
    {
        if (!$this->dianResolution) {
            $this->dianResolution()->create([
                'company_id' => $this->company_id,
                'dian_status' => 'PENDING',
                'dian_sent_at' => now()
            ]);
        } else {
            $this->dianResolution->update([
                'dian_sent_at' => now(),
                'dian_status' => 'PENDING'
            ]);
        }
    }
    
    /**
     * Marca la factura como procesada por DIAN con el resultado dado.
     *
     * @param string $status Estado devuelto por DIAN (ACCEPTED, REJECTED)
     * @param string|null $responseCode Código de respuesta
     * @param string|null $responseMessage Mensaje de respuesta
     * @return void
     */
    public function markAsProcessedByDian(string $status, ?string $responseCode = null, ?string $responseMessage = null): void
    {
        if (!$this->dianResolution) {
            $this->dianResolution()->create([
                'company_id' => $this->company_id,
                'dian_status' => $status,
                'dian_response_code' => $responseCode,
                'dian_response_message' => $responseMessage,
                'dian_sent_at' => now(),
                'dian_processed_at' => now()
            ]);
        } else {
            $this->dianResolution->update([
                'dian_processed_at' => now(),
                'dian_status' => $status,
                'dian_response_code' => $responseCode,
                'dian_response_message' => $responseMessage
            ]);
        }
    }
    
    /**
     * Obtiene el estado DIAN de la factura.
     *
     * @return string|null
     */
    public function getDianStatus(): ?string
    {
        return $this->dianResolution ? $this->dianResolution->dian_status : null;
    }
    
    /**
     * Obtiene el código de respuesta DIAN.
     *
     * @return string|null
     */
    public function getDianResponseCode(): ?string
    {
        return $this->dianResolution ? $this->dianResolution->dian_response_code : null;
    }
    
    /**
     * Obtiene el mensaje de respuesta DIAN.
     *
     * @return string|null
     */
    public function getDianResponseMessage(): ?string
    {
        return $this->dianResolution ? $this->dianResolution->dian_response_message : null;
    }
    
    /**
     * Obtiene la fecha de envío a DIAN.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDianSentAt()
    {
        return $this->dianResolution ? $this->dianResolution->dian_sent_at : null;
    }
    
    /**
     * Obtiene la fecha de procesamiento por DIAN.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDianProcessedAt()
    {
        return $this->dianResolution ? $this->dianResolution->dian_processed_at : null;
    }
    
    /**
     * Verifica si la factura requiere validación de DIAN.
     * 
     * @return bool
     */
    public function requiresDianValidation(): bool
    {
        // Solo las facturas formales requieren validación con DIAN,
        // los tickets POS quedan excluidos según la normativa
        return $this->document_type === 'invoice';
    }
    
    /**
     * Verifica si la factura puede ser enviada a DIAN.
     *
     * @return bool
     */
    public function canBeSentToDian(): bool
    {
        // Solo se pueden enviar facturas que no estén ya procesadas y que requieran validación
        return $this->requiresDianValidation() && 
               !$this->isProcessedByDian() && 
               $this->dian_retry_count < 3;
    }
} 