<?php

namespace Tests\Feature\Admin;

use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\AuthenticatesAdmin;

class StatTest extends TestCase
{
    use RefreshDatabase, AuthenticatesAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    CONST url = '/api/dashboard/stats/';

    public function test_it_returns_correct_statistics()
    {
        $token = $this->authenticateAdmin();

        $voters = Voter::factory()->count(10)->create(['is_candidate' => 0]);
        $candidates = Voter::factory()->count(5)->create(['is_candidate' => 1]);

        foreach ($voters as $voter) {
            Vote::create([
                'candidate_id' => $voter->id,
                'candidate_voted_id' => $candidates->random()->id,
                'date' => now()->format('Y-m-d'),
            ]);
        }

        $response = $this->getJson(self::url, [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'totalVoters',
            'totalVotes',
            'candidates',
            'votersWithVotes',
            'votersWithoutVotes',
            'voterNames' => [
                '*' => [
                    'id',
                    'name',
                    'lastname',
                ],
            ],
            'candidateNames' => [
                '*' => [
                    'id',
                    'name',
                    'lastname',
                ],
            ],
        ]);

        $this->assertEquals(15, $response['totalVoters']);
        $this->assertEquals(10, $response['totalVotes']);
        $this->assertEquals(5, $response['candidates']);
        $this->assertEquals(10, $response['votersWithVotes']);
        $this->assertEquals(5, $response['votersWithoutVotes']);
    }

    public function test_it_returns_candidate_votes()
    {
        $token = $this->authenticateAdmin();

        $candidates = Voter::factory()->count(5)->create(['is_candidate' => 1]);
        $voters = Voter::factory()->count(10)->create(['is_candidate' => 0]);

        foreach ($voters as $voter) {
            Vote::create([
                'candidate_id' => $voter->id,
                'candidate_voted_id' => $candidates->random()->id,
                'date' => now()->format('Y-m-d'),
            ]);
        }

        $response = $this->getJson(self::url.'candidate-votes', [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'name',
                'lastname',
                'votes'
            ]
        ]);

        $candidates->each(function($candidate) use ($response) {
            $candidateData = $response->json();
            $candidateResponse = collect($candidateData)->firstWhere('name', $candidate->name);
            $this->assertEquals($candidate->voted()->count(), $candidateResponse['votes']);
        });
    }
}
