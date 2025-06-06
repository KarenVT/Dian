<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar el seeder de roles y permisos
        $this->call([
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Limpiar caché de rutas y permisos
        Artisan::call('optimize:clear');
    }
}
