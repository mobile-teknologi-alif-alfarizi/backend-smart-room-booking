<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Get all users (Admin only)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = User::select('id', 'name', 'nomor_induk', 'role', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diambil',
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single user by ID (Admin only)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = User::select('id', 'name', 'nomor_induk', 'role', 'created_at', 'updated_at')
                ->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new user (Admin only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'nomor_induk' => 'required|string|unique:users,nomor_induk',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|in:mahasiswa,dosen,admin',
            ]);

            $user = User::create([
                'name' => $request->name,
                'nomor_induk' => $request->nomor_induk,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nomor_induk' => $user->nomor_induk,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user (Admin only)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'nomor_induk' => 'sometimes|string|unique:users,nomor_induk,' . $id,
                'password' => 'sometimes|string|min:6|confirmed',
                'role' => 'sometimes|in:mahasiswa,dosen,admin',
            ]);

            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('nomor_induk')) {
                $user->nomor_induk = $request->nomor_induk;
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->has('role')) {
                $user->role = $request->role;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nomor_induk' => $user->nomor_induk,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
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
                'message' => 'Gagal mengupdate user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user (Admin only)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            // Prevent deleting yourself
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus user sendiri',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage(),
            ], 500);
        }
    }
}
