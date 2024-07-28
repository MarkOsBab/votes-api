<?php

namespace Tests\Feature;

use App\Models\Admin;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_it_can_login_with_valid_credentials()
    {
        $admin = $this->makeAdminUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
        ], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_it_cannot_login_with_invalid_credentials()
    {
        $admin = $this->makeAdminUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ], [
            'Authorization' => 'Bearer ' . config('app.api_token'),
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Error email or password.']);
    }

    public function test_it_can_get_the_authenticated_user()
    {
        $admin = $this->makeAdminUser();
        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/auth/me');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $admin->id,
            'name' => $admin->name,
            'lastname' => $admin->lastname,
            'email' => $admin->email,
        ]);
    }

    public function test_it_cannot_get_the_authenticated_user_without_token()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_it_can_logout()
    {
        $admin = $this->makeAdminUser();
        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Successfully logged out']);
    }

    public function test_it_cannot_logout_without_token()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    public function test_it_can_refresh_the_token()
    {
        $admin = $this->makeAdminUser();
        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/auth/refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_it_cannot_refresh_the_token_without_token()
    {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401);
    }

    private function makeAdminUser()
    {
        return Admin::factory()->create();
    }
}
