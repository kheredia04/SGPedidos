<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponser;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    use ApiResponser;

    public function register(AuthRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->showOne([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 'Usuario registrado exitosamente', 201);
        } catch (\Throwable $th) {
            return $this->showError($th);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Credenciales inválidas', 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->showOne([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 'Usuario autenticado exitosamente', 200);
        } catch (\Throwable $th) {
            return $this->showError($th);
        }
    }
}
