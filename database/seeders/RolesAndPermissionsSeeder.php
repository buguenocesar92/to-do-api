<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos relacionados con productos, roles y otros módulos
        $permissions = [
            'products.index',
            'products.store',
            'products.show',
            'products.update',
            'products.destroy',
            'products.showByBarcode',
            'inventory.movements.store',
            'sales.store',
            'roles.with-permissions',               // Permiso para obtener roles con permisos
            'roles.with-permissions.show',          // Permiso para mostrar un rol con permisos
            'roles.update-permissions',             // Permiso para actualizar permisos de roles
            'permission.index',                      // Permiso para obtener todos los permisos
            'users.index',                          // Permiso para obtener todos los usuarios
            'update-roles-users',
            'cash-register.open',
            'cash-register.close',
            'cash-register.status',
            'categories.index',
            'categories.store',
            'categories.show',
            'categories.update',
            'categories.destroy',
            'inventory.movements.index',
            'roles.update-roles-users',
            'reports.sales.view_all',
            'reports.sales.view_daily',
            'reports.sales.view_monthly',
            'reports.sales.view_by_user',
            'locations.index',
            'locations.store',
            'locations.show',
            'locations.update',
            'locations.destroy',
            'warehouses.index',
            'warehouses.store',
            'warehouses.show',
            'warehouses.update',
            'warehouses.destroy',
            'warehouses.view',
            'warehouses.with-locations',
            'users.without-roles',
            'users.with-locations',
            'users.show',
            'users.update',
            'users.destroy',
            'users.store',
            'warehouses.setSalesStatus',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $roles = [
            'admin' => Permission::all(),
            'cashier' => ['sales.store', 'products.showByBarcode', 'cash-register.open', 'cash-register.close', 'cash-register.status'],
            'inventory-supervisor' => [
                'products.index', 'products.show', 'products.update', 'inventory.movements.store',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            if (is_array($permissions)) {
                $role->givePermissionTo($permissions);
            } else {
                $role->syncPermissions($permissions);
            }
        }
    }
}
