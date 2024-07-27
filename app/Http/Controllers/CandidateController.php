<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function index()
    {
        $candidates = Voter::where('is_candidate', 1)
            ->get();

        return response()->json($candidates);
    }
}
