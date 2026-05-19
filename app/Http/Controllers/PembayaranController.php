<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Transaksi;
use Carbon\Carbon;
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
            $validated = $request->validate([
                'id_transaksi'      => 'required|integer|exists:transaksi,id_transaksi',
                'metode'            => 'required|in:cash,qris,debit',
                'jumlah_pembayaran' => 'required|numeric|min:0',
            ]);

            // Cek transaksi sudah ada & statusnya masih menunggu
            $transaksi = Transaksi::with([
                'detailTransaksiLayanan.layanan',
                'detailObat',
            ])->findOrFail($validated['id_transaksi']);

            if ($transaksi->status === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi ini sudah dibayar',
                ], 400);
            }

            // Simpan pembayaran
            $pembayaran = Pembayaran::create([
                'id_transaksi'       => $validated['id_transaksi'],
                'metode'             => $validated['metode'],
                'jumlah_pembayaran'  => $validated['jumlah_pembayaran'],
                'status_pembayaran'  => 'berhasil',
                'tanggal_pembayaran' => Carbon::now()->toDateTimeString(),
            ]);

            // Update status transaksi menjadi selesai
            $transaksi->update([
                'status' => 'selesai',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil',
                'data'    => [
                    'pembayaran' => $pembayaran,
                    'transaksi'  => [
                        'id_transaksi'     => $transaksi->id_transaksi,
                        'status'           => 'selesai',
                        'subtotal_layanan' => $transaksi->subtotal_layanan,
                        'subtotal_obat'    => $transaksi->subtotal_obat,
                        'total_bayar'      => $transaksi->total_bayar,
                    ],
                ],
            ], 201);
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
