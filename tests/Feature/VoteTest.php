<?php

namespace Tests\Feature;

use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    CONST url = '/api/votes/';
    
    public function test_it_return_correct_json_structure()
    {
        $this->makeVote(5);
        $response = $this->getJson(self::url, [
           'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'votes' => [
                'data' => [
                    '*' => [
                        'id',
                        'voter' => [
                            'id',
                            'document',
                            'name',
                            'lastname',
                            'birth_day',
                            'is_candidate',
                        ],
                        'candidate' => [
                            'id',
                            'document',
                            'name',
                            'lastname',
                            'birth_day',
                            'is_candidate',
                        ],
                        'date',
                    ],
                ],
                'current_page',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ],
            'mostVoted' => [
                'id',
                'document',
                'name',
                'lastname',
                'birth_day',
                'is_candidate',
                'total',
            ],
        ]);
    }

    public function test_it_returns_error_if_voter_has_already_voted()
    {
        $voter = Voter::factory()->create();
        Vote::create([
            'candidate_id' => $voter->id,
            'candidate_voted_id' => Voter::factory()->create(['is_candidate' => 1])->id,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $response = $this->postJson(self::url, [
            'document' => $voter->document,
            'candidate' => Voter::factory()->create(['is_candidate' => 1])->id,
        ], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['error' => 'Ya has votado']);
    }

    
    public function test_it_registers_vote_if_voter_has_not_voted()
    {
        $voter = Voter::factory()->create();
        $candidate = Voter::factory()->create(['is_candidate' => 1]);

        $response = $this->postJson(self::url, [
            'document' => $voter->document,
            'candidate' => $candidate->id,
        ], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'id',
            'voter' => [
                'id',
                'document',
                'name',
                'lastname',
                'birth_day',
                'is_candidate',
            ],
            'candidate' => [
                'id',
                'document',
                'name',
                'lastname',
                'birth_day',
                'is_candidate',
            ],
            'date',
        ]);

        $this->assertDatabaseHas('votes', [
            'candidate_id' => $voter->id,
            'candidate_voted_id' => $candidate->id,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    public function test_it_validates_request_data()
    {
        $response = $this->postJson(self::url, [], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'document' => ['El documento es requerido'],
            'candidate' => ['El candidato es requerido'],
        ]);

        $response = $this->postJson(self::url, ['document' => 'non_existing_document'], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'document' => ['El documento no existe'],
        ]);

        $voter = Voter::factory()->create();

        $response = $this->postJson(self::url, ['document' => $voter->document, 'candidate' => 'non_existing_candidate'], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'candidate' => ['El candidato no existe o no es candidato'],
        ]);
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
