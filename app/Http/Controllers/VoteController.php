<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vote\StoreRequest;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/votes",
     *     summary="Obtener lista de votos",
     *     description="Devuelve una lista de votos con información sobre los votantes y candidatos, y el candidato más votado",
     *     tags={"Votes"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de votos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="votes",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Vote")
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/votes?votes=1"),
     *                 @OA\Property(property="from", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/votes?votes=1"),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/votes"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="total", type="integer", example=0)
     *             ),
     *             @OA\Property(
     *                 property="mostVoted",
     *                 type="object",
     *                 nullable=true,
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="document", type="string", example="12345678"),
     *                 @OA\Property(property="name", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe"),
     *                 @OA\Property(property="birth_day", type="string", format="date", example="2000-01-01"),
     *                 @OA\Property(property="is_candidate", type="boolean", example=true),
     *                 @OA\Property(property="total", type="integer", example=100)
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
        $votes = Vote::with(['voter:id,document,name,lastName,dob,is_candidate', 'candidate:id,document,name,lastName,dob,is_candidate'])
            ->orderByDesc('date')
            ->paginate(10, ['*'], 'votes');

        $mostVotedCandidate = Vote::select('candidate_voted_id', DB::raw('count(*) as total'))
            ->groupBy('candidate_voted_id')
            ->orderByDesc('total')
            ->with('candidate:id,document,name,lastName,dob,is_candidate')
            ->first();

        $mostVoted = $mostVotedCandidate ? array_merge($mostVotedCandidate->candidate->toArray(), ['total' => $mostVotedCandidate->total]) : null;

        $response = [
            'votes' => $votes,
            'mostVoted' => $mostVoted,
        ];

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/votes",
     *     summary="Registrar un voto",
     *     description="Registra un voto para un candidato, verificando que el votante no haya votado previamente",
     *     tags={"Votes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"document", "candidate"},
     *             @OA\Property(property="document", type="string", example="12345678"),
     *             @OA\Property(property="candidate", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voto registrado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Vote")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ya has votado o error en la validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Ya has votado"),
     *             @OA\Property(
     *                 property="document",
     *                 type="array",
     *                 @OA\Items(type="string", example="El documento no existe")
     *             ),
     *             @OA\Property(
     *                 property="candidate",
     *                 type="array",
     *                 @OA\Items(type="string", example="El candidato no existe o no es candidato")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
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
    public function store(StoreRequest $request)
    {
        $voter = Voter::where('document', $request->validated('document'))
            ->first();

        if($voter->votes()->exists())
        {
            return response()->json(['error' => 'Ya has votado'], Response::HTTP_BAD_REQUEST);
        }

        $vote = Vote::create([
            'candidate_id' => $voter->id,
            'candidate_voted_id' => $request->validated('candidate'),
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        return response()->json($vote);
    }
}
