<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * El modelo al que corresponde este factory.
     *
     * @var string
     */
    protected $model = Merchant::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nit' => fake()->numberBetween(900000000, 999999999),
            'business_name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'tax_regime' => fake()->randomElement(['COMÚN', 'SIMPLE', 'NO_RESPONSABLE_IVA']),
            'certificate_path' => null,
        ];
    }

    /**
     * Indica que el comercio tiene un certificado digital.
     *
     * @return static
     */
    public function withCertificate(): static
    {
        return $this->state(fn (array $attributes) => [
            'certificate_path' => 'certs/' . Str::slug($attributes['business_name']) . '/' . Str::uuid() . '.p12',
        ]);
    }
} 