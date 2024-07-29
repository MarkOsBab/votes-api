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
    public function index()
    {
        $votes = Vote::with(['voter:id,name', 'candidate:id,name'])
            ->orderByDesc('date')
            ->paginate($perPage = 10, $columns = ['*'], $pageName = 'votes');

        $mostVotedCandidate = Vote::select('candidate_voted_id', DB::raw('count(*) as total'))
            ->groupBy('candidate_voted_id')
            ->orderByDesc('total')
            ->with('candidate:id,name')
            ->first();

        $mostVoted = $mostVotedCandidate ? array_merge($mostVotedCandidate->candidate->toArray(), ['total' => $mostVotedCandidate->total]) : null;

        $response = [
            'votes' => $votes,
            'mostVoted' => $mostVoted,
        ];

        return response()->json($response);
    }

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
