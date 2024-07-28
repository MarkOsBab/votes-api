<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
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
                'last_name' => $candidate->lastName,
                'votes' => $candidate->voted_count,
            ];
        });

        return response()->json($response);
    }

}
