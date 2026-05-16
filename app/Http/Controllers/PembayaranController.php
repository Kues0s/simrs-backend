<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Menampilkan List Pembayaran
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
     * Menambahkan Data Pembayaran 
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'id_transaksi' => 'required|integer',
            'metode' => 'required|in:cash,debit,transfer',
            'jumlah_pembayaran' => 'required|numeric',
            'status_pembayaran' => 'required|in:pending,berhasil,gagal',
            'tanggal_pembayaran' => 'required|date',
        ]);

        try {
            $pembayaran = Pembayaran::create($validateData);
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil ditambahkan',
                'data' => $pembayaran,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menambahkan data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan Data Pembayaran Tertentu
     */
    public function show(String $id_pembayaran)
    {
        try{
            $data = Pembayaran::find($id_pembayaran);

            if(!$data){
                return response()->json([
                    'succes' => false,
                    'message' => 'Data Pembayaran tidak ditemukan',
                ],404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $data,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Data Pembayaran
     */
    public function update(Request $request, String $id_pembayaran)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'sometimes|required|integer',
            'metode' => 'sometimes|required|in:cash,debit,transfer',
            'jumlah_pembayaran' => 'sometimes|required|numeric',
            'status_pembayaran' => 'sometimes|required|in:pending,berhasil,gagal',
            'tanggal_pembayaran' => 'sometimes|required|date',
        ]);

        try {
            $data = Pembayaran::findOrFail($id_pembayaran);
            $data->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diupdate',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengupdate data pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus Data Pembayaran
     */
    public function destroy(String $id_pembayaran)
    {
        try {
            $data = Pembayaran::findOrFail($id_pembayaran);
            $data->delete();
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
