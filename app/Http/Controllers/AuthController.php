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
    /**
     *
     * @OA\Info(
     *      version="1.0.0",
     *      title="VOTES-API Documentation"
     * )
     *      
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Iniciar sesión de administrador",
     *     description="Autentica a un administrador y devuelve un token JWT",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJh..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error de correo electrónico o contraseña",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error email or password.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Ocurrió un error al iniciar sesión.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de validación incorrectos",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="array", @OA\Items(type="string", example="El email es requerido")),
     *             @OA\Property(property="password", type="array", @OA\Items(type="string", example="La contraseña es requerida"))
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="api-token-key",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         ),
     *         description="API Token Key"
     *     )
     * )
    */
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

    
    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Cerrar sesión de usuario",
     *     description="Cierra la sesión del usuario autenticado y revoca el token JWT",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cierre de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="api-token-key",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         ),
     *         description="API Token Key"
     *     )
     * )
    */
    public function logout()
    {
        auth()->logout();
  
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refrescar token JWT",
     *     description="Genera un nuevo token JWT a partir del token actual",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refrescado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJh..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="api-token-key",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         ),
     *         description="API Token Key"
     *     )
     * )
    */
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
