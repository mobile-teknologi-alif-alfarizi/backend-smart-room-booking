<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    /**
     * Display a listing of all ruangan.
     */
    public function index()
    {
        $ruangan = Ruangan::with('kampus')->orderBy('nama_ruangan', 'asc')->get();
        return response()->json($ruangan);
    }

    /**
     * Store a newly created ruangan in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kampus_id' => 'required|integer|exists:kampus,id',
            'nama_ruangan' => 'required|string|max:255|unique:ruangan,nama_ruangan,NULL,id,kampus_id,' . $request->kampus_id,
        ]);

        $ruangan = Ruangan::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil dibuat',
            'data' => $ruangan->load('kampus')
        ], 201);
    }

    /**
     * Display the specified ruangan.
     */
    public function show($id)
    {
        $ruangan = Ruangan::with('kampus')->findOrFail($id);
        return response()->json($ruangan);
    }

    /**
     * Update the specified ruangan in database.
     */
    public function update(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $validated = $request->validate([
            'kampus_id' => 'required|integer|exists:kampus,id',
            'nama_ruangan' => 'required|string|max:255|unique:ruangan,nama_ruangan,' . $id . ',id,kampus_id,' . $request->kampus_id,
        ]);

        $ruangan->update($validated);

        return response()->json([
            'message' => 'Ruangan berhasil diperbarui',
            'data' => $ruangan->load('kampus')
        ]);
    }

    /**
     * Remove the specified ruangan from database.
     */
    public function destroy($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->delete();

        return response()->json([
            'message' => 'Ruangan berhasil dihapus'
        ]);
    }
}
