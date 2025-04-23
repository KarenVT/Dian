<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'sku' => 'PROD' . fake()->unique()->numerify('######'),
            'name' => fake()->sentence(3),
            'price' => fake()->randomFloat(2, 10, 10000),
            'tax_rate' => fake()->randomElement([0, 5, 19]),
            'dian_code' => fake()->numerify('####'),
        ];
    }
    
    /**
     * Asigna un merchant específico al producto.
     *
     * @param int $merchantId
     * @return static
     */
    public function forMerchant($merchantId): static
    {
        return $this->state(fn (array $attributes) => [
            'merchant_id' => $merchantId,
        ]);
    }
}
