<?php

namespace Tests\Traits;

use App\Models\Admin; 

trait AuthenticatesAdmin
{
    protected function authenticateAdmin()
    {
        $admin = Admin::factory()->create();

        $authUser = $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password'
        ], [
            'api-token-key' => config('app.api_token'),
        ]);
        return $authUser['access_token'];
    }
}
