<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
         $this->userService = $userService;
    }

    /**
     * Listar todos los usuarios.
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        return response()->json($users);
    }

    /**
     * Obtener usuarios sin roles asignados.
     */
    public function getUsersWithoutRoles(): JsonResponse
    {
        $users = $this->userService->getUsersWithoutRoles();
        return response()->json($users);
    }

    /**
     * Obtener todos los usuarios con sus ubicaciones.
     */
    public function getAllWithLocations(): JsonResponse
    {
        $users = $this->userService->getAllWithLocations();
        return response()->json($users);
    }

    /**
     * Mostrar un usuario en específico.
     */
    public function show($id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        return response()->json($user);
    }

    /**
     * Crear un nuevo usuario.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        // Los datos ya han sido validados en el Form Request.
        $data = $request->validated();
        $user = $this->userService->createUser($data);
        return response()->json($user, 201);
    }

    /**
     * Actualizar un usuario.
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        // Los datos ya han sido validados en el Form Request.
        $data = $request->validated();
        $user = $this->userService->updateUser($id, $data);
        return response()->json($user);
    }

    /**
     * Eliminar un usuario.
     */
    public function destroy($id): JsonResponse
    {
        $this->userService->deleteUser($id);
        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}
