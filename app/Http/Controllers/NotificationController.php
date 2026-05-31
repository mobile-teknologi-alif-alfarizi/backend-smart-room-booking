<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            $query = Notification::with(['booking.ruangan.kampus'])
                ->where('user_id', $user->id)
                ->orderBy('dikirim_pada', 'desc')
                ->orderBy('id', 'desc');

            if ($request->has('status_baca')) {
                if ($request->status_baca === 'belum') {
                    $query->whereNull('dibaca_pada');
                }

                if ($request->status_baca === 'sudah') {
                    $query->whereNotNull('dibaca_pada');
                }
            }

            $notifications = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data notifikasi berhasil diambil',
                'data' => $notifications,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread notification count for authenticated user.
     */
    public function unreadCount()
    {
        try {
            $user = auth('api')->user();

            $count = Notification::where('user_id', $user->id)
                ->whereNull('dibaca_pada')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jumlah notifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead($id)
    {
        try {
            $user = auth('api')->user();

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan',
                ], 404);
            }

            if (!$notification->dibaca_pada) {
                $notification->dibaca_pada = now();
                $notification->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai sudah dibaca',
                'data' => $notification,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah notifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            $user = auth('api')->user();

            Notification::where('user_id', $user->id)
                ->whereNull('dibaca_pada')
                ->update(['dibaca_pada' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil ditandai sudah dibaca',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui notifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all notifications for admin management.
     */
    public function adminIndex()
    {
        try {
            $notifications = Notification::with(['user:id,name,nomor_induk,role', 'booking.ruangan.kampus'])
                ->orderBy('dikirim_pada', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data notifikasi admin berhasil diambil',
                'data' => $notifications,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data notifikasi admin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send manual notification by admin.
     */
    public function sendManual(Request $request)
    {
        try {
            $request->validate([
                'judul' => 'required|string|max:255',
                'pesan' => 'required|string',
                'keterangan' => 'nullable|string|max:255',
                'target_type' => 'required|in:users,role',
                'target_role' => 'required_if:target_type,role|in:mahasiswa,dosen',
                'user_ids' => 'required_if:target_type,users|array|min:1',
                'user_ids.*' => 'integer|exists:users,id',
            ]);

            $targets = collect();

            if ($request->target_type === 'role') {
                $targets = User::where('role', $request->target_role)->get();
            }

            if ($request->target_type === 'users') {
                $targets = User::whereIn('id', $request->user_ids)->get();
            }

            if ($targets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada user target untuk dikirim notifikasi',
                ], 422);
            }

            DB::beginTransaction();

            foreach ($targets as $targetUser) {
                Notification::create([
                    'user_id' => $targetUser->id,
                    'booking_id' => null,
                    'judul' => $request->judul,
                    'pesan' => $request->pesan,
                    'keterangan' => $request->keterangan,
                    'jenis' => 'admin_manual',
                    'sumber' => 'admin',
                    'dikirim_pada' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi manual berhasil dikirim',
                'data' => [
                    'total_penerima' => $targets->count(),
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi manual: ' . $e->getMessage(),
            ], 500);
        }
    }
}
