<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'merchant_id',
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
        'dian_status',
        'dian_response_code',
        'dian_response_message',
        'dian_retry_count',
        'dian_sent_at',
        'dian_processed_at',
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
        'dian_retry_count' => 'integer',
        'dian_sent_at' => 'datetime',
        'dian_processed_at' => 'datetime',
    ];

    /**
     * Obtiene el comercio al que pertenece esta factura.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
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
     * Verifica si la factura ha sido aceptada por la DIAN.
     *
     * @return bool
     */
    public function isAcceptedByDian(): bool
    {
        return $this->dian_status === 'ACCEPTED';
    }
    
    /**
     * Verifica si la factura ha sido rechazada por la DIAN.
     *
     * @return bool
     */
    public function isRejectedByDian(): bool
    {
        return $this->dian_status === 'REJECTED';
    }
    
    /**
     * Verifica si la factura está pendiente de envío a la DIAN.
     *
     * @return bool
     */
    public function isPendingDian(): bool
    {
        return $this->dian_status === 'PENDING';
    }
    
    /**
     * Verifica si la factura ha sido enviada a la DIAN y está en espera de respuesta.
     *
     * @return bool
     */
    public function isSentToDian(): bool
    {
        return $this->dian_status === 'SENT';
    }
} 