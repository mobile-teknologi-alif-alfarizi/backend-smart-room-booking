<?php

namespace App\Http\Controllers;

use App\Models\JwtSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Login user and return JWT token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'nomor_induk' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('nomor_induk', $request->nomor_induk)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor Induk atau password salah',
                ], 401);
            }

            $token = JWTAuth::fromUser($user);
            $payload = JWTAuth::setToken($token)->getPayload();
            $jti = $payload->get('jti');
            $expiresIn = auth('api')->factory()->getTTL() * 60;

            // Create JWT session record
            JwtSession::create([
                'user_id' => $user->id,
                'jti' => $jti,
                'expires_at' => now()->addSeconds($expiresIn),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'info' => 'Token berlaku selama 12 jam tanpa aktivitas. Jika masih aktif digunakan, session akan terus berlanjut.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'nomor_induk' => $user->nomor_induk,
                        'role' => $user->role,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => $expiresIn,
                    'expires_at' => now()->addSeconds($expiresIn)->toIso8601String(),
                    'inactivity_timeout' => '12 jam',
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nomor_induk' => $user->nomor_induk,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid atau expired',
            ], 401);
        }
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::setToken($token)->getPayload();
            $jti = $payload->get('jti');

            // Delete JWT session record
            JwtSession::where('jti', $jti)->delete();

            // Invalidate token
            JWTAuth::invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal',
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $oldToken = JWTAuth::getToken();
            $oldPayload = JWTAuth::setToken($oldToken)->getPayload();
            $oldJti = $oldPayload->get('jti');

            // Refresh the token
            $newToken = JWTAuth::refresh($oldToken);
            $newPayload = JWTAuth::setToken($newToken)->getPayload();
            $newJti = $newPayload->get('jti');
            $expiresIn = auth('api')->factory()->getTTL() * 60;

            // Update JWT session record with new jti
            JwtSession::where('jti', $oldJti)->update([
                'jti' => $newJti,
                'expires_at' => now()->addSeconds($expiresIn),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui',
                'info' => 'Session Anda telah diperpanjang. Token akan berlaku selama 12 jam tanpa aktivitas dari sekarang.',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $expiresIn,
                    'expires_at' => now()->addSeconds($expiresIn)->toIso8601String(),
                    'inactivity_timeout' => '12 jam',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh gagal',
            ], 401);
        }
    }
}
