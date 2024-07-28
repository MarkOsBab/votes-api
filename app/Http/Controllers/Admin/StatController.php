<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\Voter;

class StatController extends Controller
{
    public function index()
    {
        $totalVoters = Voter::count();
        $totalVotes = Vote::count();

        $candidates = Voter::where('is_candidate', 1)->count();

        $votersWithVotes = Voter::has('votes')->count();

        $votersWithoutVotes = $totalVoters - $votersWithVotes;

        $response = [
            'totalVoters' => $totalVoters,
            'totalVotes' => $totalVotes,
            'candidates' => $candidates,
            'votersWithVotes' => $votersWithVotes,
            'votersWithoutVotes' => $votersWithoutVotes,
        ];

        return response()->json($response);
    }
}
