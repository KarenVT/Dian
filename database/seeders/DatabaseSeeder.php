<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar el seeder de roles y permisos
        $this->call(RoleAndPermissionSeeder::class);

        // Crear usuarios por defecto
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@ejemplo.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $cajero = User::create([
            'name' => 'Cajero',
            'email' => 'cajero@ejemplo.com',
            'password' => Hash::make('password'),
        ]);
        $cajero->assignRole('cajero');

        $contador = User::create([
            'name' => 'Contador',
            'email' => 'contador@ejemplo.com',
            'password' => Hash::make('password'),
        ]);
        $contador->assignRole('contador');

        $cliente = User::create([
            'name' => 'Cliente',
            'email' => 'cliente@ejemplo.com',
            'password' => Hash::make('password'),
        ]);
        $cliente->assignRole('cliente');
    }
}
