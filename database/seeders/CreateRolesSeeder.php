<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleSeller = Role::firstOrCreate(['name' => 'seller']);
        $roleViewer = Role::firstOrCreate(['name' => 'viewer']);

        // Crear permisos básicos
        $permissionSell = Permission::firstOrCreate(['name' => 'sell']);
        $permissionViewInvoice = Permission::firstOrCreate(['name' => 'view_invoice']);
        $permissionReport = Permission::firstOrCreate(['name' => 'report']);

        // Asignar permisos a roles
        $roleAdmin->syncPermissions([$permissionSell, $permissionViewInvoice, $permissionReport]);
        $roleSeller->syncPermissions([$permissionSell, $permissionViewInvoice]);
        $roleViewer->givePermissionTo($permissionViewInvoice);

        // Buscar al usuario administrador por email
        $adminUser = User::where('email', 'admin@ejemplo.com')->first();
        
        // Si existe, asignarle rol de admin
        if ($adminUser) {
            $adminUser->syncRoles(['admin']);
            $this->command->info('Se asignó el rol admin al usuario: ' . $adminUser->email);
        }
    }
} 