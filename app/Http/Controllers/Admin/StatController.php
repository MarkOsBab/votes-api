<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\Voter;

class StatController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/stats",
     *     summary="Obtener estadísticas",
     *     description="Devuelve estadísticas sobre los votantes, candidatos y votos",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="totalVoters", type="integer", example=100),
     *             @OA\Property(property="totalVotes", type="integer", example=80),
     *             @OA\Property(property="candidates", type="integer", example=10),
     *             @OA\Property(property="votersWithVotes", type="integer", example=70),
     *             @OA\Property(property="votersWithoutVotes", type="integer", example=30),
     *             @OA\Property(
     *                 property="voterNames",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John"),
     *                     @OA\Property(property="lastName", type="string", example="Doe")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="candidateNames",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Jane"),
     *                     @OA\Property(property="lastName", type="string", example="Smith")
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
    public function index()
    {
        $totalVoters = Voter::count();
        $totalVotes = Vote::count();

        $candidates = Voter::where('is_candidate', 1)->count();

        $votersWithVotes = Voter::has('votes')->count();
        $votersWithoutVotes = $totalVoters - $votersWithVotes;

        $voterNames = Voter::select('id', 'name', 'lastName')
            ->get()
            ->toArray();

        $candidateNames = Voter::where('is_candidate', 1)
            ->select('id', 'name', 'lastName')
            ->get()
            ->toArray();

        $response = [
            'totalVoters' => $totalVoters,
            'totalVotes' => $totalVotes,
            'candidates' => $candidates,
            'votersWithVotes' => $votersWithVotes,
            'votersWithoutVotes' => $votersWithoutVotes,
            'voterNames' => $voterNames,
            'candidateNames' => $candidateNames,
        ];

        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/stats/candidate-votes",
     *     summary="Obtener votos por candidato",
     *     description="Devuelve una lista de candidatos con el conteo de votos que han recibido",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Votos por candidato obtenidos exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
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
    public function getCandidateVotes()
    {
        $candidates = Voter::where('is_candidate', 1)
            ->withCount('voted')
            ->get();

        $response = $candidates->map(function ($candidate) {
            return [
                'name' => $candidate->name,
                'lastname' => $candidate->lastName,
                'votes' => $candidate->voted_count
            ];
        });

        return response()->json($response);
    }
}
