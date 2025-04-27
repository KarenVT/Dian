<?php

namespace Database\Factories;

use App\Models\company;
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
            'company_id' => company::factory(),
            'name' => fake()->sentence(3),
            'price' => fake()->randomFloat(2, 10, 10000),
            'tax_rate' => fake()->randomElement([0, 5, 19]),
            'dian_code' => fake()->numerify('####'),
        ];
    }
    
    /**
     * Asigna un company específico al producto.
     *
     * @param int $companyId
     * @return static
     */
    public function forcompany($companyId): static
    {
        return $this->state(fn (array $attributes) => [
            'comap' => $companyId,
        ]);
    }
}
