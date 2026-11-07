<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $service;

    /**
     * Mempersiapkan instance AuthService sebelum setiap pengujian unit.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AuthService::class);
    }

    /**
     * Memastikan metode login menghasilkan token bearer untuk kredensial valid.
     */
    public function test_login_success(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.local',
            'password' => Hash::make('secret123'),
        ]);

        $result = $this->service->login('user@test.local', 'secret123');

        $this->assertEquals($user->id, $result['user']->id);
        $this->assertNotEmpty($result['token']);
        $this->assertEquals('Bearer', $result['token_type']);
    }

    /**
     * Memastikan metode login melempar HttpException 401 jika password salah.
     */
    public function test_login_invalid_credentials_throws(): void
    {
        User::factory()->create([
            'email' => 'user@test.local',
            'password' => Hash::make('secret123'),
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->login('user@test.local', 'wrong_pass');
    }

    /**
     * Memastikan metode logout menghapus seluruh token aktif pengguna.
     */
    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token');

        $this->assertCount(1, $user->tokens);

        $this->service->logout($user);
        $this->assertCount(0, $user->fresh()->tokens);
    }
}

