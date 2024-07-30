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

    CONST url = '/api/auth/';

    public function test_it_can_login_with_valid_credentials()
    {
        $admin = $this->makeAdminUser();

        $response = $this->postJson(self::url.'login', [
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

        $response = $this->postJson(self::url.'login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Error email or password.']);
    }

    public function test_login_without_credentials()
    {
        $response = $this->postJson(self::url.'login', [], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'email' => [
                'El email es requerido',
            ],
            'password' => [
                'La contraseña es requerida'
            ]
        ]);
    }

    public function test_login_with_invalid_email()
    {
        $creadentials = [
            'email' => 'test',
            'password' => 'password',
        ];

        $response = $this->postJson(self::url.'login', $creadentials, [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'email' => [
                'El email no es válido',
            ],
        ]);
    }

    public function test_it_can_get_the_authenticated_user()
    {
        $admin = $this->makeAdminUser();
        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'api-token-key' => config('app.api_token'),
            'Authorization' => "Bearer $token",
        ])->getJson(self::url.'me');

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
        $response = $this->getJson(self::url.'me');

        $response->assertStatus(401);
    }

    public function test_it_can_logout()
    {
        $admin = $this->makeAdminUser();
        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'api-token-key' => config('app.api_token'),
            'Authorization' => "Bearer $token",
        ])->postJson(self::url.'logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Successfully logged out']);
    }

    public function test_it_cannot_logout_without_token()
    {
        $response = $this->postJson(self::url.'logout');

        $response->assertStatus(401);
    }

    public function test_it_can_refresh_the_token()
    {
        $admin = $this->makeAdminUser();
        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'api-token-key' => config('app.api_token'),
            'Authorization' => "Bearer $token",
        ])->postJson(self::url.'refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_it_cannot_refresh_the_token_without_token()
    {
        $response = $this->postJson(self::url.'refresh', [], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(401);
    }

    private function makeAdminUser()
    {
        return Admin::factory()->create();
    }
}
