<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Registra un nuevo usuario y devuelve sus datos.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        return $user->toArray();
    }

    /**
     * Intenta autenticar al usuario y devuelve un token JWT o null si falla.
     *
     * @param array $credentials
     * @return string|null
     */
    public function login(array $credentials): ?string
    {
        // Se utiliza explícitamente el guard 'api' para autenticación JWT.
        if (Auth::guard('api')->attempt($credentials)) {
            return Auth::guard('api')->tokenById(Auth::guard('api')->id());
        }
        return null;
    }

    /**
     * Prepara y retorna la respuesta JSON con el token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken(string $token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
