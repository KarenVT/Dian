<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DianResolution extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dian_resolutions';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'resolution_id';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'invoice_id',
        'dian_status',
        'dian_response_code',
        'dian_response_message',
        'dian_retry_count',
        'dian_sent_at',
        'dian_processed_at',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dian_sent_at' => 'datetime',
        'dian_processed_at' => 'datetime',
        'dian_retry_count' => 'integer',
    ];

    /**
     * Obtiene la empresa a la que pertenece esta resolución.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Obtiene la factura a la que pertenece esta resolución.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Verifica si la resolución ha sido enviada a la DIAN.
     */
    public function isSent(): bool
    {
        return !is_null($this->dian_sent_at);
    }

    /**
     * Verifica si la resolución ha sido procesada por la DIAN.
     */
    public function isProcessed(): bool
    {
        return !is_null($this->dian_processed_at);
    }

    /**
     * Verifica si la resolución ha sido aceptada por la DIAN.
     */
    public function isAccepted(): bool
    {
        return strtoupper($this->dian_status) === 'ACCEPTED';
    }

    /**
     * Verifica si la resolución ha sido rechazada por la DIAN.
     */
    public function isRejected(): bool
    {
        return strtoupper($this->dian_status) === 'REJECTED';
    }

    /**
     * Verifica si la resolución está pendiente de procesamiento por la DIAN.
     */
    public function isPending(): bool
    {
        return strtoupper($this->dian_status) === 'PENDING';
    }

    /**
     * Incrementa el contador de reintentos.
     */
    public function incrementRetryCount(): void
    {
        $this->dian_retry_count++;
        $this->save();
    }
}
