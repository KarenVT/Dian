<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'product_id',
        'payment_method',
        'seller',
        'software_description',
        'quantity',
        'unit_value',
        'total_value',
        'subtotal',
        'discounts',
        'surcharges',
        'total_base',
        'tax_rate',
        'tax',
        'total',
    ];

    /**
     * Los atributos que deben convertirse a tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discounts' => 'decimal:2',
        'surcharges' => 'decimal:2',
        'total_base' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns this detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the product associated with this detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate and set the total value based on quantity and unit value.
     *
     * @return void
     */
    public function calculateTotalValue(): void
    {
        $this->total_value = $this->quantity * $this->unit_value;
    }

    /**
     * Calculate and set all financial values (subtotal, tax, total).
     *
     * @return void
     */
    public function calculateFinancials(): void
    {
        // Calcular el subtotal (valor unitario * cantidad)
        $this->subtotal = $this->unit_value * $this->quantity;
        
        // Base imponible (subtotal - descuentos + recargos)
        $this->total_base = $this->subtotal - $this->discounts + $this->surcharges;
        
        // Calcular impuesto
        $this->tax = $this->total_base * ($this->tax_rate / 100);
        
        // Total final
        $this->total = $this->total_base + $this->tax;
    }
}
