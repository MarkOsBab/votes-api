<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\PasswordHistory;
use App\Models\Voter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\AuthenticatesAdmin;

class ManageTest extends TestCase
{
    use RefreshDatabase, AuthenticatesAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    CONST url = '/api/dashboard/management/';

    public function test_it_changes_password_successfully()
    {
        $adminPassword = 'Password.';
        $admin = Admin::factory()->create([
            'password' => Hash::make($adminPassword),
        ]);

        $authUser = $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => $adminPassword
        ], [
            'api-token-key' => config('app.api_token'),
        ]);

        $response = $this->postJson(self::url.'change-password', [
            'currentPassword' => $adminPassword,
            'newPassword' => 'New.Password.1',
        ], [
            'Authenticate' => 'Bearer '.$authUser['access_token'],
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Clave actualizada exitosamente',
        ]);
    }

    public function test_it_fails_when_current_password_is_incorrect()
    {
        $token = $this->authenticateAdmin();

        $response = $this->postJson(self::url.'change-password', [
            'currentPassword' => 'wrongpassword',
            'newPassword' => 'NewSecret123!',
        ], [
            'Authenticate' => 'Bearer '.$token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Clave actual incorrecta',
        ]);
    }

    public function test_it_fails_when_new_password_is_reused()
    {
        $adminPassword = 'Password.';
        $admin = Admin::factory()->create([
            'password' => Hash::make($adminPassword),
        ]);

        $authUser = $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => $adminPassword
        ], [
            'api-token-key' => config('app.api_token'),
        ])->json();

        PasswordHistory::create(['admin_id' => $admin->id, 'password' => Hash::make($adminPassword)]);

        $response = $this->postJson(self::url.'change-password', [
            'currentPassword' => $adminPassword,
            'newPassword' => $adminPassword,
        ], [
            'Authorization' => 'Bearer '.$authUser['access_token'],
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Ya has utilizado esta clave',
        ]);
    }

    public function test_it_validates_request_data()
    {
        $token = $this->authenticateAdmin();

        $response = $this->postJson(self::url.'change-password', [], [
            'Authenticate' => 'Bearer '.$token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'currentPassword' => ['La contraseña actual es requerida'],
            'newPassword' => ['La nueva contraseña es requerida'],
        ]);

        $response = $this->postJson(self::url.'change-password', [
            'currentPassword' => 'secret',
            'newPassword' => 'short',
        ], [
            'Authenticate' => 'Bearer '.$token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'newPassword' => ['La nueva contraseña debe tener mínimo 6 caracteres'],
        ]);

        $response = $this->postJson(self::url.'change-password', [
            'currentPassword' => 'secret',
            'newPassword' => str_repeat('a', 17),
        ], [
            'Authenticate' => 'Bearer '.$token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'newPassword' => ['La nueva contraseña debe tener máximo 16 caracteres'],
        ]);

        $response = $this->postJson(self::url.'change-password', [
            'currentPassword' => 'secret',
            'newPassword' => 'alllowercase',
        ], [
            'Authenticate' => 'Bearer '.$token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'newPassword' => ['La nueva contraseña debe contener al menos una letra mayúscula y un símbolo'],
        ]);

        $response = $this->postJson(self::url.'change-password', [
            'currentPassword' => 'secret',
            'newPassword' => 'NoSymbol123',
        ], [
            'Authenticate' => 'Bearer '.$token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'newPassword' => ['La nueva contraseña debe contener al menos una letra mayúscula y un símbolo'],
        ]);
    }

    public function test_it_creates_a_voter_successfully()
    {
        $token = $this->authenticateAdmin();

        $response = $this->postJson(self::url.'create-voters', [
            'document' => '123456789',
            'name' => 'John',
            'lastName' => 'Doe',
            'dob' => '1990-01-01',
            'is_candidate' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'document',
            'name',
            'lastname',
            'birth_day',
            'is_candidate',
        ]);
    }

    public function test_it_fails_when_required_fields_are_missing()
    {
        $token = $this->authenticateAdmin();

        $response = $this->postJson(self::url.'create-voters', [], [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'document' => ['El documento es requerido'],
            'name' => ['El nombre es requerido'],
            'lastName' => ['El apellido es requerido'],
            'dob' => ['La fecha de nacimiento es requerida'],
            'is_candidate' => ['El candidato o votante es requerido'],
        ]);
    }

    public function test_it_fails_when_document_is_not_unique()
    {
        $token = $this->authenticateAdmin();

        Voter::create([
            'document' => '123456789',
            'name' => 'John',
            'lastName' => 'Doe',
            'dob' => '1990-01-01',
            'is_candidate' => 1,
        ]);

        $response = $this->postJson(self::url.'create-voters', [
            'document' => '123456789',
            'name' => 'Jane',
            'lastName' => 'Smith',
            'dob' => '1992-02-02',
            'is_candidate' => 0,
        ], [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'document' => ['El documento ya está registrado'],
        ]);
    }

    public function test_it_fails_when_fields_are_too_short()
    {
        $token = $this->authenticateAdmin();

        $response = $this->postJson(self::url.'create-voters', [
            'document' => '1',
            'name' => 'Jo',
            'lastName' => 'Do',
            'dob' => '1990-01-01',
            'is_candidate' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'name' => ['El nombre debe contener mínimo 3 caracteres'],
            'lastName' => ['El apellido debe contener mínimo 3 caracteres'],
        ]);
    }

    public function test_it_fails_when_dob_is_invalid()
    {
        $token = $this->authenticateAdmin();

        $response = $this->postJson(self::url.'create-voters', [
            'document' => '123456789',
            'name' => 'John',
            'lastName' => 'Doe',
            'dob' => 'invalid-date',
            'is_candidate' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'dob' => ['La fecha de nacimiento tiene un formato incorrecto'],
        ]);
    }

    public function test_it_fails_when_is_candidate_is_invalid()
    {
        $token = $this->authenticateAdmin();

        $response = $this->postJson(self::url.'create-voters', [
            'document' => '123456789',
            'name' => 'John',
            'lastName' => 'Doe',
            'dob' => '1990-01-01',
            'is_candidate' => 2,
        ], [
            'Authorization' => 'Bearer ' . $token,
            'api-token-key' => config('app.api_token'),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'is_candidate' => ['El candidato o votante tiene un formato inválido'],
        ]);
    }

}
