<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Get all booking with user and ruangan.kampus relation.
     */
    public function index()
    {
        try {
            $bookings = Booking::with(['user', 'ruangan.kampus'])
                ->orderBy('tanggal', 'desc')
                ->orderBy('waktu_mulai', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data booking berhasil diambil',
                'data' => $bookings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data booking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bookings for authenticated user.
     */
    public function myBookings()
    {
        try {
            $user = auth('api')->user();

            $bookings = Booking::with(['ruangan.kampus'])
                ->where('user_id', $user->id)
                ->orderBy('tanggal', 'desc')
                ->orderBy('waktu_mulai', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data booking pengguna berhasil diambil',
                'data' => $bookings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data booking pengguna: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new booking.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'ruangan_id' => 'required|integer|exists:ruangan,id',
                'tanggal' => 'required|date',
                'waktu_mulai' => 'required|date_format:H:i',
                'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
                'keperluan' => 'required|string|max:255',
                'tipe_booking' => 'required|in:jadwal_kelas,peminjaman_mandiri',
            ]);

            if ($request->waktu_mulai < '07:00' || $request->waktu_selesai > '17:00') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking hanya diperbolehkan antara jam 07:00 hingga 17:00',
                ], 422);
            }

            $isOverlapping = Booking::where('ruangan_id', $request->ruangan_id)
                ->where('tanggal', $request->tanggal)
                ->where('status', '!=', 'rejected')
                ->where(function ($query) use ($request) {
                    $query->where('waktu_mulai', '<', $request->waktu_selesai)
                        ->where('waktu_selesai', '>', $request->waktu_mulai);
                })
                ->exists();

            if ($isOverlapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan sudah dibooking pada jam tersebut',
                ], 422);
            }

            $booking = Booking::create([
                'user_id' => $request->user_id,
                'ruangan_id' => $request->ruangan_id,
                'tanggal' => $request->tanggal,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'keperluan' => $request->keperluan,
                'tipe_booking' => $request->tipe_booking,
                'status' => 'approved',
            ]);

            Notification::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'judul' => 'Booking berhasil disetujui',
                'pesan' => 'Booking ruangan pada tanggal ' . $booking->tanggal . ' jam ' . $booking->waktu_mulai . ' - ' . $booking->waktu_selesai . ' berhasil disetujui otomatis.',
                'keterangan' => 'Notifikasi sistem setelah booking auto-approved',
                'jenis' => 'booking_success',
                'sumber' => 'system',
                'dikirim_pada' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat',
                'data' => $booking->load(['user', 'ruangan.kampus']),
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
                'message' => 'Gagal membuat booking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel booking for authenticated user.
     */
    public function cancel($id)
    {
        try {
            $user = auth('api')->user();

            $booking = Booking::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking tidak ditemukan',
                ], 404);
            }

            if ($booking->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking sudah dibatalkan sebelumnya',
                ], 422);
            }

            $booking->status = 'rejected';
            $booking->save();

            Notification::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'judul' => 'Booking berhasil dibatalkan',
                'pesan' => 'Booking tanggal ' . $booking->tanggal . ' jam ' . $booking->waktu_mulai . ' - ' . $booking->waktu_selesai . ' telah dibatalkan.',
                'keterangan' => 'Notifikasi sistem setelah pembatalan booking oleh pengguna',
                'jenis' => 'booking_success',
                'sumber' => 'system',
                'dikirim_pada' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibatalkan',
                'data' => $booking->load(['user', 'ruangan.kampus']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan booking: ' . $e->getMessage(),
            ], 500);
        }
    }
}
