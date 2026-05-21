<?php

namespace App\Http\Controllers;

use App\Models\Kampus;
use Illuminate\Http\Request;

class KampusController extends Controller
{
    /**
     * Get all kampus
     */
    public function index()
    {
        $kampus = Kampus::all();
        return response()->json([
            'success' => true,
            'data' => $kampus
        ]);
    }

    /**
     * Get single kampus
     */
    public function show($id)
    {
        $kampus = Kampus::find($id);
        
        if (!$kampus) {
            return response()->json([
                'success' => false,
                'message' => 'Kampus tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $kampus
        ]);
    }

    /**
     * Create new kampus
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kampus' => 'required|string|max:255|unique:kampus,nama_kampus',
            'alamat' => 'required|string|max:1000',
        ]);

        $kampus = Kampus::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kampus berhasil ditambahkan',
            'data' => $kampus
        ], 201);
    }

    /**
     * Update kampus
     */
    public function update(Request $request, $id)
    {
        $kampus = Kampus::find($id);

        if (!$kampus) {
            return response()->json([
                'success' => false,
                'message' => 'Kampus tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nama_kampus' => 'required|string|max:255|unique:kampus,nama_kampus,' . $id,
            'alamat' => 'required|string|max:1000',
        ]);

        $kampus->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kampus berhasil diupdate',
            'data' => $kampus
        ]);
    }

    /**
     * Delete kampus
     */
    public function destroy($id)
    {
        $kampus = Kampus::find($id);

        if (!$kampus) {
            return response()->json([
                'success' => false,
                'message' => 'Kampus tidak ditemukan'
            ], 404);
        }

        $kampus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kampus berhasil dihapus'
        ]);
    }
}
