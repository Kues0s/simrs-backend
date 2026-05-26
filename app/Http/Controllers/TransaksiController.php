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
            $transaksi = Transaksi::with('pembayaran')->get();
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
        try {
            $validated = $request->validate([
                'pasien_id'  => 'required|integer',
                'id_antrian' => 'required|integer',
                'id_rm'      => 'required|integer',
                'id_dokter'  => 'required|integer', 
                'id_perawat' => 'required|integer', 
                'id_resep'   => 'nullable|integer',
            ]);

            // 1. Hit API kelompok 2 → biaya dokter
            $biayaDokter    = 0;
            $dokterResponse = Http::get(env('K2_API_BASE_URL') . '/dokter/' . $validated['id_dokter'] . '/biaya');
            if ($dokterResponse->successful()) {
                $biayaDokter = $dokterResponse->json('data.biaya_layanan');
            }

            // 2. Hit API kelompok 2 → biaya perawat
            $biayaPerawat    = 0;
            $perawatResponse = Http::get(env('K2_API_BASE_URL') . '/perawat/' . $validated['id_perawat'] . '/biaya');
            if ($perawatResponse->successful()) {
                $biayaPerawat = $perawatResponse->json('data.biaya_layanan');
            }

            // 3. Simpan transaksi
            $transaksi = Transaksi::create([
                'pasien_id'  => $validated['pasien_id'],
                'id_antrian' => $validated['id_antrian'],
                'id_rm'      => $validated['id_rm'],
                'id_dokter'  => $validated['id_dokter'],
                'id_perawat' => $validated['id_perawat'],
                'id_resep'   => $validated['id_resep'] ?? null,
                'tanggal'    => Carbon::now()->toDateTimeString(),
                'status'     => 'menunggu',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data'    => [
                    'transaksi'     => $transaksi,
                    'biaya_dokter'  => $biayaDokter,
                    'biaya_perawat' => $biayaPerawat,
                    'subtotal_jasa' => $biayaDokter + $biayaPerawat,
                ],
            ], 201);

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
     * Menampilkan Data Transaksi Tertentu
     */
    public function show($id_transaksi)
    {
        try {
            $transaksi = Transaksi::with('pembayaran',)->findOrFail($id_transaksi);

            // 1. Hit API kelompok 2 → biaya dokter
            $biayaDokter = 0;
            $namaDokter = '-';
            $dokterResponse = Http::get(env('K2_API_BASE_URL') . '/dokter/' . $transaksi->id_dokter);
            if ($dokterResponse->successful()) {
                $biayaDokter = $dokterResponse->json('data.biaya_layanan');
                $namaDokter = $dokterResponse->json('data.nama_dokter');
            }

            // 2. Hit API kelompok 2 → biaya perawat
            $biayaPerawat = 0;
            $namaPerawat = '-';
            $perawatResponse = Http::get(env('K2_API_BASE_URL') . '/perawat/' . $transaksi->id_perawat);
            if ($perawatResponse->successful()) {
                $biayaPerawat = $perawatResponse->json('data.biaya_layanan');
                $namaPerawat = $perawatResponse->json('data.nama_perawat');
            }

            // 3. Hit API kelompok 4 → detail obat
            $detailObat   = collect();
            $subtotalObat = 0;

            if ($transaksi->id_resep) {
                $resepResponse = Http::get(env('K4_API_BASE_URL') . '/e-resep/' . $transaksi->id_resep);
                if ($resepResponse->successful()) {
                    $resepData = $resepResponse->json();
                    $detailResep = $resepData[0]['detail_resep'] ?? [];
                    $detailObat = collect($detailResep)->map(function ($item) {
                        return [
                            'id_obat'    => $item['ID_OBAT'],
                            'nama_obat'  => $item['obat']['NAMA_OBAT'],
                            'jumlah'     => $item['JUMLAH'],
                            'harga_jual' => $item['obat']['HARGA_JUAL'],
                            'total_harga'   => $item['JUMLAH'] * $item['obat']['HARGA_JUAL'],
                        ];
                    });

                    $subtotalObat = $detailObat->sum('total_harga');
                }
            }

            // 4. Hitung total
            $totalBayar = $biayaDokter + $biayaPerawat + $subtotalObat;

            return response()->json([
                'success' => true,
                'message' => 'Transaksi ditemukan',
                'data'    => [
                    'transaksi' => [
                        'id_transaksi' => $transaksi->id_transaksi,
                        'pasien_id'    => $transaksi->pasien_id,
                        'id_rm'        => $transaksi->id_rm,
                        'id_antrian'   => $transaksi->id_antrian,
                        'id_dokter'   => $transaksi->id_dokter,
                        'nama_dokter' => $namaDokter,
                        'id_perawat'  => $transaksi->id_perawat,
                        'nama_perawat' => $namaPerawat,
                        'id_resep'    => $transaksi->id_resep,
                        'tanggal'      => $transaksi->tanggal,
                        'status'       => $transaksi->status,
                    ],
                    'detail_obat' => $detailObat,
                    'ringkasan'      => [
                        'biaya_dokter' => $biayaDokter,
                        'biaya_perawat' => $biayaPerawat,
                        'subtotal_obat'    => $subtotalObat,
                        'total_bayar'      => $totalBayar,
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
    public function update(Request $request, $id_transaksi)
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
                'id_resep'    => 'sometimes|nullable|integer',
            ]);
            
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
    public function destroy($id_transaksi)
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
    public function getTransaksiByIdAntrian(String $id_antrian)
    {
        try {
            $transaksi = Transaksi::where('id_antrian', $id_antrian)->with('pembayaran')
            ->where('status', 'menunggu')
            ->latest()
            ->firstOrFail();

            // 1. Hit API kelompok 2 → biaya dokter
            $biayaDokter = 0;
            $dokterResponse = Http::get(env('K2_API_BASE_URL') . '/dokter/' . $transaksi->id_dokter . '/biaya');
            if ($dokterResponse->successful()) {
                $biayaDokter = $dokterResponse->json('data.biaya_layanan');
            }

            // 2. Hit API kelompok 2 → biaya perawat
            $biayaPerawat = 0;
            $perawatResponse = Http::get(env('K2_API_BASE_URL') . '/perawat/' . $transaksi->id_perawat . '/biaya');
            if ($perawatResponse->successful()) {
                $biayaPerawat = $perawatResponse->json('data.biaya_layanan');
            }

            // 3. Hit API kelompok 4 → detail obat
            $detailObat   = collect();
            $subtotalObat = 0;

            if ($transaksi->id_resep) {
                $resepResponse = Http::get(env('K4_API_BASE_URL') . '/resep/' . $transaksi->id_resep);
                if ($resepResponse->successful()) {
                    $detailResep = $resepResponse->json('data.detail_resep');

                    $detailObat = collect($detailResep)->map(function ($item) {
                        return [
                            'id_obat'    => $item['id_obat'],
                            'nama_obat'  => $item['nama_obat'],
                            'jumlah'     => $item['jumlah'],
                            'harga_jual' => $item['harga_jual'],
                            'subtotal'   => $item['jumlah'] * $item['harga_jual'],
                        ];
                    });

                    $subtotalObat = $detailObat->sum('subtotal');
                }
            }

            // 4. Hitung total
            $totalBayar = $biayaDokter + $biayaPerawat + $subtotalObat;

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditemukan',
                'data'    => [
                    'transaksi' => [
                        'id_transaksi' => $transaksi->id_transaksi,
                        'pasien_id'    => $transaksi->pasien_id,
                        'id_rm'        => $transaksi->id_rm,
                        'id_antrian'   => $transaksi->id_antrian,
                        'id_dokter'   => $transaksi->id_dokter,
                        'id_perawat'  => $transaksi->id_perawat,
                        'id_resep'    => $transaksi->id_resep,
                        'tanggal'      => $transaksi->tanggal,
                        'status'       => $transaksi->status,
                    ],
                    'detail_obat' => $detailObat,
                    'ringkasan'      => [
                        'biaya_dokter' => $biayaDokter,
                        'biaya_perawat' => $biayaPerawat,
                        'subtotal_obat'    => $subtotalObat,
                        'total_bayar'      => $totalBayar,
                    ],
                    'pembayaran'     => $transaksi->pembayaran,
                ],
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
