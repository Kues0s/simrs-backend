<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    /**
     * Menampilkan Seluruh Layanan.
     */
    public function index()
    {
        try{
            $layanan = Layanan::all();
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diambil',
                'data' => $layanan,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menambahkan Layanan Baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_layanan' => 'required|string',
            'tarif_dokter' => 'required|numeric',
            'tarif_perawat' => 'required|numeric',
            'status_layanan' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $layanan = Layanan::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil dibuat',
                'data' => $layanan,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan satu buah Layanan .
     */
    public function show(String $id_layanan)
    {
        try{
            $layanan = Layanan::findOrFail($id_layanan);
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diambil',
                'data' => $layanan, 
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Data Layanan Berdasarkan ID_layanan.
     */
    public function update(Request $request, String $id_layanan)
    {
        try{
            $validatedData = $request->validate([
                'nama_layanan' => 'sometimes|required|string',
                'tarif_dokter' => 'sometimes|required|numeric',
                'tarif_perawat' => 'sometimes|required|numeric',
                'status_layanan' => 'sometimes|required|in:aktif,nonaktif',
            ]);
            $data = Layanan::findOrFail($id_layanan);
            $data->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diupdate',
                'data' => $data,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengupdate data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus Layanan.
     */
    public function destroy(String $id_layanan)
    {
        try{
            $data = Layanan::findOrFail($id_layanan);
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil dihapus',
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal menghapus data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
