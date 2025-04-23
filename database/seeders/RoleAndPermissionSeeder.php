<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos (abilities)
        $permissions = [
            'view_invoice',
            'view_invoice_own',
            'sell',
            'report',
            'manage_users',
            'manage_products',
            'manage_merchants',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $cajeroRole = Role::create(['name' => 'cajero']);
        $cajeroRole->givePermissionTo(['sell', 'view_invoice']);

        $contadorRole = Role::create(['name' => 'contador']);
        $contadorRole->givePermissionTo(['view_invoice', 'report']);

        $clienteRole = Role::create(['name' => 'cliente']);
        $clienteRole->givePermissionTo(['view_invoice_own']);
    }
} 