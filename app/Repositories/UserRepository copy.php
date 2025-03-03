<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Crear un usuario.
     *
     * @param array $data Datos del usuario.
     * @return User Usuario creado.
     */
    public function create(array $data): User
    {
        // Verificar si se proporciona una contraseña y encriptarla
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $this->model->create($data);
    }


    /**
     * Obtener todos los usuarios.
     *
     * @return Collection Colección de usuarios.
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Obtener usuarios sin roles asignados.
     */
    public function getUsersWithoutRoles(): Collection
    {
        return $this->model->doesntHave('roles')->get();
    }

    /**
     * Obtener todos los usuarios con sus ubicaciones.
     */
    public function getAllWithLocations(): Collection
    {
        return $this->model->with('location')->get();
    }

    /**
     * Encontrar un usuario por su ID.
     */
    public function find($id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Actualizar un usuario.
     */
    /**
     * Actualizar un usuario.
     */
    public function update($id, array $data): User
    {
        $user = $this->model->findOrFail($id);

        // Si location_id está en los datos, lo asignamos
        if (isset($data['location_id'])) {
            $user->location_id = $data['location_id'];
        }

        // Si se proporciona una nueva contraseña, la encriptamos
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            // Si no se proporciona, la eliminamos para evitar sobreescritura
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }



    /**
     * Eliminar un usuario.
     */
    public function delete($id): bool
    {
        $user = $this->model->findOrFail($id);
        return $user->delete();
    }
}
