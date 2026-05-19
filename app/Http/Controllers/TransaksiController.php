<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransaksiController extends Controller
{
    /**
     * Menampilkan List Data Transaksi.
     */
    public function index()
    {
        try{
            $transaksi = Transaksi::all();
            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi',
                'data' => $transaksi
            ],200);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * Menambahkan Data Transaksi Baru.
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'pasien_id' => 'required|integer',
                'id_antrian' => 'nullable|integer',
                'id_rm' => 'nullable|integer',
                'id_resep' => 'nullable|integer',
            ]);

            // LANGKAH 2: Verifikasi NIK ke API Kelompok 1
            // $cekPasien = Http::timeout(5)
            //     ->withToken($request->bearerToken()) // ← pakai token kasir yang login
            //     ->get("http://kelompok1.local/api/pasien", [
            //         'pasien_id' => $validated['pasien_id']
            //     ]);

            // if ($cekPasien->failed() || empty($cekPasien->json('data'))) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Pasien_id tidak ditemukan di sistem',
            //     ], 404);
            // }

            // $dataPasien = $cekPasien->json('data');

           // LANGKAH 4: Simpan ke DB
            $transaksi = Transaksi::create([
                'pasien_id'   => $validated['pasien_id'],
                'id_antrian'  => $validated['id_antrian'] ?? null,
                'id_rm'       => $validated['id_rm'] ?? null,
                'id_resep'    => $validated['id_resep'] ?? null,
                'tanggal'     => Carbon::now()->toDateTimeString(),
                'status'      => 'menunggu',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data'    => $transaksi,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa terhubung ke sistem kelompok 1',
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan Data Transaksi Tertentu
     */
    public function show($id_transaksi)
    {
        try {
            $transaksi = Transaksi::with([
                'detailTransaksiLayanan.layanan',
                'detailTransaksiObat',
                'pembayaran',
            ])->findOrFail($id_transaksi);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi ditemukan',
                'data'    => [
                    'transaksi' => [
                        'id_transaksi' => $transaksi->id_transaksi,
                        'pasien_id'    => $transaksi->pasien_id,
                        'id_rm'        => $transaksi->id_rm,
                        'id_antrian'   => $transaksi->id_antrian,
                        'tanggal'      => $transaksi->tanggal,
                        'status'       => $transaksi->status,
                    ],
                    'detail_layanan' => $transaksi->detailTransaksiLayanan->map(function ($item) {
                        return [
                            'nama_layanan'   => $item->layanan->nama_layanan,
                            'jumlah_layanan' => $item->jumlah_layanan,
                            'tarif_dokter'   => $item->layanan->tarif_dokter,
                            'tarif_perawat'  => $item->layanan->tarif_perawat,
                            'subtotal'       => $item->subtotal,
                        ];
                    }),
                    'detail_obat'    => $transaksi->detailTransaksiObat->map(function ($item) {
                        return [
                            'id_obat'     => $item->id_obat,
                            'jumlah_obat' => $item->jumlah_obat,
                            // harga & nama obat menyusul dari kelompok 4
                        ];
                    }),
                    'ringkasan'      => [
                        'subtotal_layanan' => $transaksi->subtotal_layanan,
                        'subtotal_obat'    => $transaksi->subtotal_obat,
                        'total_bayar'      => $transaksi->total_bayar,
                    ],
                    'pembayaran'     => $transaksi->pembayaran,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Memperbaharui Data Transaksi Berdasarkan id_transaksi
     */
    public function update(Request $request, String $id_transaksi)
    {
        try {
            $transaksi = Transaksi::findOrFail($id_transaksi);

            // Cek apakah transaksi boleh diupdate
            if ($transaksi->status === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi yang sudah selesai tidak bisa diupdate',
                ], 422);
            }

            $validated = $request->validate([
                'id_rm'       => 'sometimes|nullable|integer',
                'id_resep'    => 'sometimes|nullable|integer',
                'tanggal'     => 'sometimes|date',
                'status'      => 'sometimes|in:menunggu,selesai',
            ]);

            // Jika id_rm diupdate, verifikasi ke API kelompok 2
            // if (!empty($validated['id_rm'])) {
            //     $cekRm = Http::timeout(5)
            //         ->withToken($request->bearerToken())
            //         ->get("http://kelompok2.local/api/rekam-medik/{$validated['id_rm']}");

            //     if ($cekRm->failed()) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Rekam medis tidak ditemukan',
            //         ], 404);
            //     }
            // }

            // Jika id_resep diupdate, verifikasi ke API kelompok 2
            // if (!empty($validated['id_resep'])) {
            //     $cekResep = Http::timeout(5)
            //         ->withToken($request->bearerToken())
            //         ->get("http://kelompok2.local/api/e-resep/{$validated['id_resep']}");

            //     if ($cekResep->failed()) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Resep tidak ditemukan',
            //         ], 404);
            //     }
            // }

            $transaksi->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diupdate',
                'data'    => $transaksi,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa terhubung ke sistem kelompok lain',
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Menghapus Data Transaksi
     */
    public function destroy(String $id_transaksi)
    {
        try {
            $transaksi = Transaksi::findOrFail($id_transaksi);
            
            // Cek apakah transaksi boleh dihapus
            // Hanya transaksi dengan status 'menunggu' yang boleh dihapus
            if ($transaksi->status !== 'menunggu') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi yang sudah selesai atau batal tidak bisa dihapus',
                ], 422);
            }
            $transaksi->delete();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
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
     * Menampilkan Statistik Transaksi
     */
   public function jumlahTransaksi()
    {
        // Hitung transaksi hari ini
        $hariIni = Transaksi::whereDate('tanggal', Carbon::today())->count();

        // Hitung transaksi bulan ini
        $bulanIni = Transaksi::whereMonth('tanggal', Carbon::now()->month)
                            ->whereYear('tanggal', Carbon::now()->year)
                            ->count();

        // Gabungkan dalam satu response JSON
        return response()->json([
            'success' => true,
            'message' => 'Statistik Jumlah Transaksi',
            'data' => [
                'total_hari_ini'  => $hariIni,
                'total_bulan_ini' => $bulanIni,
                // Kamu bisa tambah total_tahun_ini atau total_semua di sini nanti
            ]
        ]);
    }

    /**
     * Menampilkan Transaksi Berdasarkan id_rm
     */
    public function getTransaksiByIdRm(String $id_rm)
    {
        try {
            $transaksi = Transaksi::where('id_rm', $id_rm)->with([
                'detailTransaksiLayanan.layanan',
                'detailTransaksiObat',
                'pembayaran'
            ])->get();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditemukan',
                'data'    => $transaksi,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
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
