<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'merchant_id',
        'sku',
        'name',
        'price',
        'tax_rate',
        'dian_code',
    ];

    /**
     * Los atributos que deben convertirse a tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    /**
     * Obtiene el comercio al que pertenece este producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
