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
