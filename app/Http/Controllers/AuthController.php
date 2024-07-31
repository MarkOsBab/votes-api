<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = Auth::attempt($credentials)) {
                Log::warning('Intento de inicio de sesión fallido para el correo: ' . $request->email);
                return response()->json(['error' => 'Error email or password.'], Response::HTTP_UNAUTHORIZED);
            }
            
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            Log::error('Error en el inicio de sesión: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al iniciar sesión.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function logout()
    {
        auth()->logout();
  
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
