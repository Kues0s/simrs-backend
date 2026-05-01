<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use Illuminate\Http\Request;

class DetailTransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $detailTransaksi = DetailTransaksi::all();
            return response()->json([
                'success' => true,
                'message' => 'Data detail transaksi berhasil diambil',
                'data' => $detailTransaksi,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data detail transaksi',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'required|integer',
            'id_layanan' => 'nullable|integer',
            'id_obat' => 'nullable|integer',
            'jenis' => 'required|in:layanan,obat',
            'harga_detail' => 'required|numeric',
            'jumlah' => 'required|integer',
            'total' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $detailTransaksi = DetailTransaksi::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data detail transaksi berhasil dibuat',
                'data' => $detailTransaksi,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat data detail transaksi',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DetailTransaksi $detailTransaksi)
    {
        try{
            $detailTransaksi = DetailTransaksi::findOrFail($detailTransaksi->id_detail);
            return response()->json([
                'success' => true,
                'message' => 'Data detail transaksi berhasil diambil',
                'data' => $detailTransaksi,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data detail transaksi',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DetailTransaksi $detailTransaksi)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|integer',
            'id_layanan' => 'nullable|integer',
            'id_obat' => 'nullable|integer',
            'jenis' => 'sometimes|required|in:layanan,obat',
            'harga_detail' => 'sometimes|required|numeric',
            'jumlah' => 'sometimes|required|integer',
            'total' => 'sometimes|required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $detailTransaksi = DetailTransaksi::findOrFail($detailTransaksi->id_detail);
            $detailTransaksi->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data detail transaksi berhasil diupdate',
                'data' => $detailTransaksi,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengupdate data detail transaksi',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetailTransaksi $detailTransaksi)
    {
        try{
            $detailTransaksi = DetailTransaksi::findOrFail($detailTransaksi->id_detail);
            $detailTransaksi->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data detail transaksi berhasil dihapus',
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal menghapus data detail transaksi',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
