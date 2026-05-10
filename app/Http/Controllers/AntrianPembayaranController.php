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
        $request->validate([
            'id_transaksi' => 'required|integer',
            'no_antrian_pay' => 'required|string',
            'status_antrian' => 'required|in:menunggu,dipanggil,selesai',
            'waktu_masuk' => 'required|date',
        ]);

        try {
            $antrianPembayaran = AntrianPembayaran::create($request->all());
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
    public function show(AntrianPembayaran $antrianPembayaran)
    {
        try{
            $antrianPembayaran = AntrianPembayaran::findOrFail($antrianPembayaran->id_antrian_pay);
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
    public function update(Request $request, AntrianPembayaran $antrianPembayaran)
    {
        try{
            $validatedData = $request->validate([
                'id_transaksi' => 'integer',
                'no_antrian_pay' => 'string',
                'status_antrian' => 'in:menunggu,dipanggil,selesai',
                'waktu_masuk' => 'date',
            ]);
            $antrianPembayaran = AntrianPembayaran::findOrFail($antrianPembayaran->id_antrian_pay);
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
    public function destroy(AntrianPembayaran $antrianPembayaran)
    {
        $antrianPembayaran = AntrianPembayaran::findOrFail($antrianPembayaran->id_antrian_pay);
        try{
            $antrianPembayaran->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data antrian pembayaran berhasil dihapus',
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal menghapus data antrian pembayaran',
                'message' => $e->getMessage()
            ], 500);    
        }
    }
}
