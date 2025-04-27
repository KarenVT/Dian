<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'document_type',
        'document_number',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
    ];

    /**
     * Obtiene la empresa a la que pertenece este cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Alias para mantener compatibilidad con código existente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getCompany(): BelongsTo
    {
        return $this->company();
    }
}
