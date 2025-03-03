<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use App\Repositories\PermissionRepository;


class RolePermissionService
{
    private RoleRepository $roleRepository;
    private PermissionRepository $permissionRepository;

    public function __construct(RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Obtener todos los roles con sus permisos asociados.
     */
    public function getRolesWithPermissions(): array
    {
        return $this->roleRepository->getAllWithPermissions()->toArray();
    }

    public function getRoleWithPermissions(int $roleId): array
    {
        return $this->roleRepository->findWithPermissions($roleId);
    }

    /**
     * Actualizar los permisos activos de un rol.
     */
    public function updateRolePermissions(int $roleId, array $permissions): array
    {
        return $this->roleRepository->updatePermissions($roleId, $permissions);
    }
}
