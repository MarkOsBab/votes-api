<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePassword;
use App\Http\Requests\Admin\CreateVoterRequest;
use App\Models\Voter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/dashboard/management/change-password",
     *     summary="Cambiar la contraseña del administrador",
     *     description="Permite que un administrador cambie su contraseña, verificando la contraseña actual y asegurándose de que la nueva contraseña no sea una de las últimas tres usadas",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"currentPassword", "newPassword"},
     *             @OA\Property(property="currentPassword", type="string", example="CurrentPassword123"),
     *             @OA\Property(property="newPassword", type="string", example="NewPassword!2024")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña cambiada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Clave actualizada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Clave actual incorrecta"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="currentPassword",
     *                     type="array",
     *                     @OA\Items(type="string", example="La contraseña actual es requerida")
     *                 ),
     *                 @OA\Property(
     *                     property="newPassword",
     *                     type="array",
     *                     @OA\Items(type="string", example="La nueva contraseña debe tener mínimo 6 caracteres")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer {token}"
     *         ),
     *         description="Token de autorización Bearer"
     *     )
     * )
    */
    public function changePassword(ChangePassword $request)
    {
        $admin = Auth::user();

        if (!Hash::check($request->currentPassword, $admin->password)) {
            return response()->json(['message' => 'Clave actual incorrecta'], Response::HTTP_BAD_REQUEST);
        }

        $lastThreePasswords = $admin->passwordHistories()
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->pluck('password');

        foreach ($lastThreePasswords as $oldPassword) {
            if (Hash::check($request->newPassword, $oldPassword)) {
                return response()->json(['message' => 'Ya has utilizado esta clave'], Response::HTTP_BAD_REQUEST);
            }
        }

        $admin->storePasswordInHistory();

        $admin->password = Hash::make($request->newPassword);
        $admin->save();

        return response()->json(['message' => 'Clave actualizada exitosamente'], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/management/create-voters",
     *     summary="Crear un nuevo votante",
     *     description="Permite crear un nuevo votante (que puede ser un candidato)",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "lastName", "document", "dob", "is_candidate"},
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="lastName", type="string", example="Doe"),
     *             @OA\Property(property="document", type="string", example="12345678"),
     *             @OA\Property(property="dob", type="string", format="date", example="2000-01-01"),
     *             @OA\Property(property="is_candidate", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Votante creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Voter")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El nombre es requerido")
     *                 ),
     *                 @OA\Property(
     *                     property="lastName",
     *                     type="array",
     *                     @OA\Items(type="string", example="El apellido es requerido")
     *                 ),
     *                 @OA\Property(
     *                     property="document",
     *                     type="array",
     *                     @OA\Items(type="string", example="El documento ya está registrado")
     *                 ),
     *                 @OA\Property(
     *                     property="dob",
     *                     type="array",
     *                     @OA\Items(type="string", example="La fecha de nacimiento es requerida")
     *                 ),
     *                 @OA\Property(
     *                     property="is_candidate",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo candidato o votante es requerido")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer {token}"
     *         ),
     *         description="Token de autorización Bearer"
     *     )
     * )
    */
    public function crateVoter(CreateVoterRequest $request)
    {
        $voter = Voter::create([
            'document' => $request->validated('document'),
            'name' => $request->validated('name'),
            'lastName' => $request->validated('lastName'),
            'dob' => $request->validated('dob'),
            'is_candidate' => $request->validated('is_candidate'),
        ]);

        return response()->json($voter);
    }
}