<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vote\StoreRequest;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VoteController extends Controller
{
    public function index()
    {
        $votes = Vote::orderByDesc('date')
            ->get();

        return response()->json($votes);
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
