<?php

namespace Tests\Feature;

use App\Models\Voter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CandidateTest extends TestCase
{
    CONST url = '/api/candidates/';
    public function test_response_structure()
    {
        $candidates = $this->makeCandidates(1);
        $response = $this->getJson(self::url, [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            [
                'id' => $candidates[0]->id,
                'name' => $candidates[0]->name,
                'lastname' => $candidates[0]->lastName,
                'document' => $candidates[0]->document,
                'birth_day' => $candidates[0]->dob,
                'is_candidate' => $candidates[0]->is_candidate
            ]
        ]);
    }

    private function makeCandidates($quantity)
    {
        return Voter::factory()->count($quantity)->create(['is_candidate' => 1]);
    }
}
