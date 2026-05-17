<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksiLayanan;
use Illuminate\Http\Request;

class DetailTransaksiLayananController extends Controller
{
    /**
     * Menampilkan List Detail Transaksi Layanan.
     */
    public function index()
    {
        try{
            //Query Menampilkan Data 
            $data = DetailTransaksiLayanan::with('layanan')->get();

            //Return Response dalam bentuk json
            return response()->json([
                'succes' => true,
                'message' => 'Data transaksi layanan berhasil dimuat',
                'data' => $data,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Terdapat Kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menambahkan Data Transakasi Layanan 
     */
    public function store(Request $request)
    {
        // Validasi: layanan berupa array
        $validateData = $request->validate([
            'id_transaksi'          => 'required|integer',
            'layanan'               => 'required|array',
            'layanan.*.id_layanan'  => 'required|integer',
            'layanan.*.jumlah_layanan' => 'required|integer',
        ]);

        try {
            $savedData = [];
            foreach ($validateData['layanan'] as $item) {
                $savedData[] = DetailTransaksiLayanan::create([
                    'id_transaksi'   => $validateData['id_transaksi'],
                    'id_layanan'     => $item['id_layanan'],
                    'jumlah_layanan' => $item['jumlah_layanan'],
                ]);
            }

            return response()->json([
                'succes'  => true,
                'message' => 'Data berhasil disimpan',
                'data'    => $savedData,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Terdapat Kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan Detail_transaksi_layanan tertentu
     */
    public function show(String $id_detail_layanan)
    {
        //Cari data berdasarkan id
        $data = DetailTransaksiLayanan::with([
            'layanan',
            'transaksi',    
        ])->find($id_detail_layanan);

        //Jika tidak ada data
        if(!$data){
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
        ], 404);
        }

        //jika data ada
        return response()->json([
            'succes' => true,
            'message' => 'Berhasil memuat data berdasarkan id',
            'data' => $data,
        ], 200);
    }

    /**
     * Update Data_transaksi_layanan
     */
    public function update(Request $request, String $id)
    {
        // 1. Mencari data dulu berdasarkan ID
        $data = DetailTransaksiLayanan::find($id);

        // jika data tidak ada
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        // 2. Validasi data yang di request
        $validatedData = $request->validate([
            'id_layanan'     => 'required|integer',
            'id_transaksi'   => 'required|integer',
            'jumlah_layanan' => 'required|integer',
        ]);

        // 3. Update data ke database
        $data->update($validatedData);

        // 4. Kembalikan response sukses
        return response()->json([
            'success' => true,
            'message' => 'Data detail transaksi berhasil diupdate',
            'data'    => $data
        ]);
    }

    /**
     * Menghapus Berdasarkan id_detail_layanan
     */
    public function destroy(String $id)
    {
        $data = DetailTransaksiLayanan::find($id);

        if(!$data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan',
            ],404);
        }

        $data->delete();
        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil dihapus',
        ],200);
    }

/**
 * Menghapus berdasarkan id transaksi
 */

    public function destroyByTransaksi($id_transaksi)
    {
        try {
            $deleted = DetailTransaksiLayanan::where('id_transaksi', $id_transaksi)->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Semua layanan dalam transaksi berhasil dihapus',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Terdapat Kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

