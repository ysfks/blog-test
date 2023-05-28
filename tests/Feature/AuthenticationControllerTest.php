<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthenticationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');
    }
    /**
     * A basic feature test example.
     */
    public function test_user_can_register(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ];

        $response = $this->post('/api/register', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'access_token'
            ]);
    }

    public function test_user_can_login(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ];

        $response = $this->post('/api/register', $data);

        $json = $response->json();

        $login = $this->post('/api/login', $data, [
            'Authentication' => "Bearer {$json['access_token']}"
        ]);

        $login->assertStatus(200)
            ->assertJsonStructure([
                'access_token'
            ]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $this->expectException(ValidationException::class);
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ];

        $response = $this->post('/api/register', $data);

        $json = $response->json();

        $data['password'] = 'pass';

        $r = $this->post('/api/login', $data, [
            'Authentication' => "Bearer {$json['access_token']}"
        ]);

        $r->assertStatus(302)
            ->assertJson([
                'email' => __('auth.failed'),
            ]);
    }
}
