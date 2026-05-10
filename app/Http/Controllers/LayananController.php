<?php

namespace App\Http\Controllers;

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
            'kategori' => 'required|string',
            'harga' => 'required|numeric',
            'status_layanan' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $cekUnit = http::timeout(5)
                ->withToken($request->bearerToken()) // ← pakai token kasir yang login
                ->get("http://kelompok1.local/api/unit", [
                    'nama_unit' => $validatedData['kategori']
                ]);
            if ($cekUnit->failed() || empty($cekUnit->json('data'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori unit tidak ditemukan di sistem',
                ], 404);
            }
            
            $dataUnit = $cekUnit->json('data');
            $idUnit = $dataUnit[0]['id']; // ← ambil id unit
            $validatedData['kategori'] = $idUnit; // Ganti kategori dengan id unit
                
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
    public function show($id_layanan)
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
    public function update(Request $request, Layanan $id_layanan)
    {
        try{
            $validatedData = $request->validate([
                'nama_layanan' => 'sometimes|required|string',
                'kategori' => 'sometimes|required|string',
                'harga' => 'sometimes|required|numeric',
                'status_layanan' => 'sometimes|required|in:aktif,nonaktif',
            ]);
            $id_layanan->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diupdate',
                'data' => $id_layanan,
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
    public function destroy(Layanan $id_layanan)
    {
        try{
            $id_layanan->delete();
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
