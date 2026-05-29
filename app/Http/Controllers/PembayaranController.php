<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $pembayaran = Pembayaran::with('transaksi')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data pembayaran berhasil diambil',
            'data' => $pembayaran,
        ], 200);
    }


    /**
     * Menambahkan Data Pembayaran 
     */
   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_transaksi'      => 'required|integer|exists:transaksi,id_transaksi',
                'metode'            => 'required|in:cash,qris,debit',
                'total_tagihan'     => 'required|numeric|min:0', 
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

            // Cek jumlah pembayaran cukup
            if ($validated['jumlah_pembayaran'] < $validated['total_tagihan']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran kurang dari total tagihan',
                    'data'    => [
                        'total_tagihan'     => $validated['total_tagihan'],
                        'jumlah_pembayaran' => $validated['jumlah_pembayaran'],
                        'kurang'            => $validated['total_tagihan'] - $validated['jumlah_pembayaran'],
                    ]
                ], 400);
            }

            // Hitung kembalian
            $kembalian = $validated['jumlah_pembayaran'] - $validated['total_tagihan']; 

            DB::beginTransaction();

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

            // 3. Hit API kelompok 1 → update status antrian
            if ($transaksi->id_antrian) {
                $antriResponse = Http::put(
                    env('K1_API_BASE_URL') . '/antrian/' . $transaksi->id_antrian . '/status',
                    ['status' => 'lunas'] // ⚠️ sesuaikan nama status kelompok 1
                );

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
                        'total_tagihan'      => $validated['total_tagihan'], 
                        'jumlah_pembayaran'  => $pembayaran->jumlah_pembayaran,
                        'kembalian'          => $kembalian,
                        'status_pembayaran'  => $pembayaran->status_pembayaran,
                        'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                    ],
                    'transaksi'  => [
                        'id_transaksi' => $transaksi->id_transaksi,
                        'status'       => 'selesai',
                    ],
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
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
        try {
            $pembayaran = Pembayaran::with('transaksi')->findOrFail($id_pembayaran);

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data'    => $pembayaran,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Data Pembayaran
     */
    public function update(Request $request, String $id_pembayaran)
    {
        try {
            $validated = $request->validate([
                'metode'            => 'sometimes|required|in:cash,qris,debit',
                'jumlah_pembayaran' => 'sometimes|required|numeric|min:0',
                'status_pembayaran' => 'sometimes|required|in:pending,berhasil,gagal',
            ]);

            $pembayaran = Pembayaran::findOrFail($id_pembayaran);

            // Cek pembayaran sudah berhasil
            if ($pembayaran->status_pembayaran === 'berhasil') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran yang sudah berhasil tidak bisa diupdate',
                ], 400);
            }

            $pembayaran->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diupdate',
                'data'    => $pembayaran,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus Data Pembayaran
     */
    public function destroy(String $id_pembayaran)
    {
        try {
            $pembayaran = Pembayaran::findOrFail($id_pembayaran);

            // Cek pembayaran sudah berhasil
            if ($pembayaran->status_pembayaran === 'berhasil') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran yang sudah berhasil tidak bisa dihapus',
                ], 400);
            }

            $pembayaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil dihapus',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
