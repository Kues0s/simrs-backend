<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

        // Cek transaksi
        $transaksi = Transaksi::findOrFail($validated['id_transaksi']);

        if ($transaksi->status === 'selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini sudah dibayar',
            ], 400);
        }

        DB::beginTransaction();

        try {
            // 1. Simpan pembayaran
            $pembayaran = Pembayaran::create([
                'id_transaksi'       => $validated['id_transaksi'],
                'metode'             => $validated['metode'],
                'jumlah_pembayaran'  => $validated['jumlah_pembayaran'],
                'status_pembayaran'  => 'berhasil',
                'tanggal_pembayaran' => Carbon::now()->toDateTimeString(),
            ]);

            // 2. Update status transaksi → selesai
            $transaksi->update(['status' => 'selesai']);

            // 3. Hit API kelompok 1 → update status antrian → lunas
            if ($transaksi->id_antrian) {
                $antriResponse = Http::put(
                    env('K1_API_BASE_URL') . '/antrian/' . $transaksi->id_antrian . '/status',
                    ['status' => 'lunas'] // ⚠️ sesuaikan nama status kelompok 1
                );

                // Jika gagal → rollback semua!
                if ($antriResponse->failed()) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran gagal karena tidak dapat mengupdate status antrian',
                        'error'   => $antriResponse->body(),
                    ], 500);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil',
                'data'    => [
                    'pembayaran' => [
                        'id_pembayaran'      => $pembayaran->id_pembayaran,
                        'id_transaksi'       => $pembayaran->id_transaksi,
                        'metode'             => $pembayaran->metode,
                        'jumlah_pembayaran'  => $pembayaran->jumlah_pembayaran,
                        'status_pembayaran'  => $pembayaran->status_pembayaran,
                        'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                    ],
                    'transaksi'  => [
                        'id_transaksi' => $transaksi->id_transaksi,
                        'status'       => 'selesai',
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
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
