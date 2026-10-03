<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    /**
     * User login and Sanctum token generation
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors(), 422);
        }

        $user = User::with('department')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Kredensial tidak valid', [
                'auth' => ['Email atau kata sandi yang Anda masukkan salah.']
            ], 401);
        }

        if (!$user->is_active) {
            return $this->sendError('Akun dinonaktifkan', [
                'auth' => ['Akun Anda berstatus nonaktif. Silakan hubungi Administrator.']
            ], 403);
        }

        // Generate Sanctum API token with role capability
        $token = $user->createToken('orderflow_api_token', [$user->role])->plainTextToken;

        $data = [
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ];

        return $this->sendResponse($data, 'Login berhasil. Token otorisasi diterbitkan.', 200);
    }

    /**
     * Get authenticated user profile
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('department');
        return $this->sendResponse(new UserResource($user), 'Profil pengguna berhasil diambil');
    }

    /**
     * Logout and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logout berhasil. Sesi token telah dicabut.');
    }
}
