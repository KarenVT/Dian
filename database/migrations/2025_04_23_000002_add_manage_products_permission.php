<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear el permiso si no existe
        Permission::findOrCreate('manage products', 'web');
        
        // Añadir el permiso al rol 'admin' si existe
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo('manage products');
        }
        
        // Crear un rol de gestor de productos si no existe
        $productManagerRole = Role::findOrCreate('product manager', 'web');
        
        // Añadir el permiso de gestión de productos al rol de gestor de productos
        $productManagerRole->givePermissionTo('manage products');
        
        // Añadir permisos adicionales de visualización si existen
        if (Permission::where('name', 'view dashboard')->exists()) {
            $productManagerRole->givePermissionTo('view dashboard');
        }
        
        if (Permission::where('name', 'view reports')->exists()) {
            $productManagerRole->givePermissionTo('view reports');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el permiso de todos los roles
        $permission = Permission::where('name', 'manage products')->where('guard_name', 'web')->first();
        
        if ($permission) {
            // Eliminar el permiso de todos los roles
            $roles = Role::all();
            foreach ($roles as $role) {
                $role->revokePermissionTo($permission);
            }
            
            // Eliminar el permiso
            $permission->delete();
        }
        
        // Eliminar el rol de gestor de productos si existe y no tiene otros permisos
        $productManagerRole = Role::where('name', 'product manager')->where('guard_name', 'web')->first();
        if ($productManagerRole && $productManagerRole->permissions->count() <= 3) {
            $productManagerRole->delete();
        }
    }
}; 