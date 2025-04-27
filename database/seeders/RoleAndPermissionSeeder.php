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
            'manage_products',
            'view_reports_basic',
            'manage_users',
            'manage_roles',
            'manage_companies',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        // 1. Admin - acceso total
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 2. Comerciante - abilities: sell, view_invoice, manage_products, view_reports_basic
        $comercianteRole = Role::firstOrCreate(['name' => 'comerciante']);
        $comercianteRole->syncPermissions([
            'sell', 
            'view_invoice', 
            'manage_products', 
            'view_reports_basic'
        ]);

        // 3. Cliente - ability limitado: view_invoice_own
        $clienteRole = Role::firstOrCreate(['name' => 'cliente']);
        $clienteRole->syncPermissions(['view_invoice_own']);
    }
} 