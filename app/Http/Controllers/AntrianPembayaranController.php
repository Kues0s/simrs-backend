<?php

namespace App\Http\Controllers;

use App\Models\AntrianPembayaran;
use Illuminate\Http\Request;

class AntrianPembayaranController extends Controller
{
    /**
     * Menampilkan Seluruh Antrian.
     */
    public function index()
    {
        try{
            $antrianPembayaran = AntrianPembayaran::all();
            return response()->json([
                'success' => true,
                'message' => 'Data antrian pembayaran berhasil diambil',
                'data' => $antrianPembayaran,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data antrian pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menambahkan Antrian Baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'required|integer',
            'no_pembayaran' => 'required|integer',
            'status_antrian' => 'required|in:menunggu,dipanggil,selesai',
            'waktu_masuk' => 'required|date',
        ]);

        try {
            $antrianPembayaran = AntrianPembayaran::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data antrian pembayaran berhasil dibuat',
                'data' => $antrianPembayaran,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat data antrian pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan sebuah antrian.
     */
    public function show(String $id)
    {
        try{
            $antrianPembayaran = AntrianPembayaran::with('transaksi')->findOrFail($id);
            return response()->json([
                'success' => true,  
                'message' => 'Data antrian pembayaran berhasil diambil',
                'data' => $antrianPembayaran,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data antrian pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Antrian Pemabayaran (digunakan update status antrian).
     */
    public function update(Request $request, String $id)
    {
        try{
            $validatedData = $request->validate([
                'id_transaksi' => 'sometimes|required|integer',
                'no_pembayaran' => 'sometimes|required|integer',
                'status_antrian' => 'sometimes|required|in:menunggu,dipanggil,selesai',
                'waktu_masuk' => 'sometimes|required|date',
            ]);

            $antrianPembayaran = AntrianPembayaran::findOrFail($id);
            $antrianPembayaran->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Data antrian pembayaran berhasil diperbarui',
                'data' => $antrianPembayaran,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal memperbarui data antrian pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus Antrian berdasarkan id_antrian.
     */
    public function destroy($id)
    {
        try{
            $antrianPembayaran = AntrianPembayaran::findOrFail($id);
            $antrianPembayaran->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data antrian pembayaran berhasil dihapus',
                'data' => $antrianPembayaran,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal menghapus data antrian pembayaran',
                'message' => $e->getMessage()
            ], 500);    
        }
    }
}
