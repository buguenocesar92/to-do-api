<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Services\RolePermissionService;
use Illuminate\Http\JsonResponse;

class RolePermissionController extends Controller
{
    private RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * Obtener todos los roles con sus permisos asociados.
     */
    public function index(): JsonResponse
    {
        $rolesWithPermissions = $this->rolePermissionService->getRolesWithPermissions();
        return response()->json(['roles' => $rolesWithPermissions]);
    }

    /**
     * Mostrar un rol específico con sus permisos.
     */
    public function show(int $roleId): JsonResponse
    {
        $role = $this->rolePermissionService->getRoleWithPermissions($roleId);
        return response()->json($role);
    }

    /**
     * Actualizar los permisos activos de un rol específico.
     */
    public function updateRolePermissions(UpdateRolePermissionsRequest $request, int $roleId): JsonResponse
    {
        $validatedData = $request->validated();

        $updatedRole = $this->rolePermissionService->updateRolePermissions($roleId, $validatedData['permissions']);

        return response()->json([
            'message' => 'Role permissions updated successfully.',
            'role' => $updatedRole,
        ]);
    }
}
