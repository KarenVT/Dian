<?php

namespace App\DTOs;

class CartDTO
{
    /**
     * @var array Array de items en el carrito
     */
    public array $items;
    
    /**
     * @var float Subtotal del carrito
     */
    public float $subtotal;
    
    /**
     * @var float Impuestos aplicados
     */
    public float $tax;
    
    /**
     * @var float Total del carrito (subtotal + impuestos)
     */
    public float $total;
    
    /**
     * @var string Tipo de documento (income, credit, debit)
     */
    public string $type;
    
    /**
     * @var string|null Notas adicionales
     */
    public ?string $notes;
    
    /**
     * @var string|null Fecha de vencimiento (formato Y-m-d)
     */
    public ?string $dueDate;
    
    /**
     * Constructor del DTO del carrito
     *
     * @param array $items
     * @param float $subtotal
     * @param float $tax
     * @param float $total
     * @param string $type
     * @param string|null $notes
     * @param string|null $dueDate
     */
    public function __construct(
        array $items,
        float $subtotal,
        float $tax,
        float $total,
        string $type = 'income',
        ?string $notes = null,
        ?string $dueDate = null
    ) {
        $this->items = $items;
        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->total = $total;
        $this->type = $type;
        $this->notes = $notes;
        $this->dueDate = $dueDate;
    }
    
    /**
     * Crear una instancia de CartDTO desde un array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['items'] ?? [],
            $data['subtotal'] ?? 0,
            $data['tax'] ?? 0,
            $data['total'] ?? 0,
            $data['type'] ?? 'income',
            $data['notes'] ?? null,
            $data['dueDate'] ?? null
        );
    }
} 