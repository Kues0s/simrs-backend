<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $pembayaran = Pembayaran::all();
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $pembayaran,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required|integer',
            'metode' => 'required|in:cash, debit, transfer',
            'jumlah_pembayaran' => 'required|numeric',
            'tanggal_pembayaran' => 'required|date',
        ]);

        try {
            $pembayaran = Pembayaran::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil dibuat',
                'data' => $pembayaran,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembayaran $id_pembayaran)
    {
        try{
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $id_pembayaran,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pembayaran $id_pembayaran)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|integer',
            'metode' => 'sometimes|required|in:cash, debit, transfer',
            'jumlah_pembayaran' => 'sometimes|required|numeric',
            'tanggal_pembayaran' => 'sometimes|required|date',
        ]);

        try {
            $id_pembayaran->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diupdate',
                'data' => $id_pembayaran,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengupdate data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembayaran $id_pembayaran)
    {
        try {
            $id_pembayaran->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
