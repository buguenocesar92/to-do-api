<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function getAll()
    {
        return Role::all();
    }

    public function find(int $roleId)
    {
        return Role::findOrFail($roleId);
    }

    public function create(array $data)
    {
        return Role::create($data);
    }

    public function update(int $roleId, array $data)
    {
        $role = Role::findOrFail($roleId);
        $role->update($data);
        return $role;
    }

    public function delete(int $roleId): void
    {
        Role::findOrFail($roleId)->delete();
    }

    public function assignToUser(int $userId, int $roleId): void
    {
        $role = Role::findOrFail($roleId);
        $role->users()->attach($userId); // O usar sync() si es necesario
    }


    public function getAllWithPermissions(): Collection
    {
        $permissionNames = config('permissions');

        $roles = Role::with(['permissions:id,name', 'users:id,name,email'])->get()->map(function ($role) use ($permissionNames) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(function ($permission) use ($permissionNames) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'readable_name' => $permissionNames[$permission->name] ?? $permission->name,
                    ];
                }),
                'users' => $role->users->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
            ];
        });

        return new Collection($roles); // Convierte la Support\Collection en Eloquent\Collection
    }



    public function findWithPermissions(int $roleId): array
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($roleId);
        $permissionNames = config('permissions'); // Cargamos el diccionario de permisos

        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->map(function ($permission) use ($permissionNames) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'readable_name' => $permissionNames[$permission->name] ?? $permission->name, // Nombre legible
                ];
            }),
            'users' => $role->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }),
        ];
    }


    /**
    * Actualizar los permisos de un rol.
    */
    public function updatePermissions(int $roleId, array $permissions): array
    {
        $role = Role::findOrFail($roleId);
        $role->syncPermissions($permissions); // Sincroniza los permisos, eliminando los que no estén en la lista
        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ]),
        ];
    }

    public function updateUsers(int $roleId, array $userIds): void
    {
        $role = Role::findOrFail($roleId);
        $role->users()->sync($userIds); // Sincroniza los usuarios, eliminando los que no estén en la lista
    }
}
