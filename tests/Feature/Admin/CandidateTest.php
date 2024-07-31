<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\AuthenticatesAdmin;

class CandidateTest extends TestCase
{
    use RefreshDatabase, AuthenticatesAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    CONST url = '/api/dashboard/candidates/';

    public function test_it_return_correct_json_structure()
    {
        $this->makeVote(5);
        $token = $this->authenticateAdmin();

        $response = $this->getJson(self::url.'most-voted', [
            'Authenticate' => 'Bearer '.$token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'lastname',
                'votes',
            ]
        ]);
    }

    private function makeAdminUser()
    {
        return Admin::factory()->create();
    }

    private function makeVote($quantity)
    {
        $votes = array();
        $candidates = Voter::factory()->count($quantity)->create(['is_candidate' => 1]);

        foreach($candidates as $voter) {
            $hasVoted = Vote::where('candidate_id', $voter->id)->exists();
            
            if (!$hasVoted) {
                $randomCandidate = $candidates->random();
                
                $votes = Vote::create([
                    'candidate_id' => $voter->id,
                    'candidate_voted_id' => $randomCandidate->id,
                    'date' => Carbon::now()->format('Y-m-d'),
                ]);
            }
        }
        return $votes;
    }
}
