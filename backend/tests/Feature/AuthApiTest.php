<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan pengguna dapat login menggunakan email dan password yang valid.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@finance.local',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@finance.local',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user' => ['id', 'email', 'name'], 'token', 'token_type'],
            ]);
    }

    /**
     * Memastikan login ditolak dengan status 401 jika password salah.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@finance.local',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@finance.local',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Memastikan pengguna terotentikasi dapat melihat profil diri dan melakukan logout.
     */
    public function test_authenticated_user_can_get_profile_and_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        // Get profile (me)
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}

