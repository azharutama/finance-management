<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Inisialisasi controller dengan dependency AuthService.
     */
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Memproses otentikasi login pengguna dan mengembalikan token akses.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->authService->login(
            $validated['email'],
            $validated['password']
        );

        return ApiResponse::success([
            'user' => $result['user'],
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Logged in successfully.');
    }

    /**
     * Mencabut token akses sesi pengguna yang sedang login (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    /**
     * Mengambil informasi profil pengguna yang sedang terotentikasi.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $request->user(),
            'Authenticated user retrieved successfully.'
        );
    }
}
