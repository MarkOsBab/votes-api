<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/candidates/most-voted",
     *     summary="Obtener los candidatos más votados",
     *     description="Devuelve una lista de los candidatos más votados con su información y el conteo de votos",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de candidatos más votados",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe"),
     *                 @OA\Property(property="votes", type="integer", example=150)
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
    public function getMostVoted()
    {
        $candidates = Voter::where('is_candidate', 1)
            ->withCount('voted')
            ->orderBy('voted_count', 'desc')
            ->get();

        $response = $candidates->map(function($candidate) {
            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'lastname' => $candidate->lastName,
                'votes' => $candidate->voted_count,
            ];
        });

        return response()->json($response);
    }

}
