<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Ejecuta el seed para crear el usuario administrador por defecto.
     */
    public function run(): void
    {
        // Asegurarse de que existe el rol Admin
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Crear el usuario admin por defecto
        $admin = User::firstOrCreate(
            ['email' => 'admin@ejemplo.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@ojemplo.como',
                'password' => Hash::make('password'),
            ]
        );
        
        // Asignar rol de administrador
        $admin->assignRole($adminRole);
        
        $this->command->info('Usuario administrador creado exitosamente:');
        $this->command->info('Email: admin@ejemplo.com');
        $this->command->info('Contraseña: password');
    }
} 