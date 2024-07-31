<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/candidates",
     *     summary="Obtener lista de candidatos",
     *     description="Devuelve una lista de votantes que son candidatos",
     *     tags={"Candidates"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de candidatos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Voter")
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
    public function index()
    {
        $candidates = Voter::where('is_candidate', 1)
            ->get();

        return response()->json($candidates);
    }
}
