<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksiObat;
use Illuminate\Http\Request;

class DetailTransaksiObatController extends Controller
{
    /**
     * Menampilkan LIst Transaksi Obat
     */
    public function index()
    {
        $data = DetailTransaksiObat::all();
        return response()->json([
            'succes' => true,
            'message' => 'Data Transkasi Obat berhasil ditemukan',
            'data' => $data,
        ], 200);
    }

    /**
     * Menamabhkan data transaksi obat.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'id_transaksi' => 'required|integer',
            'obat' => 'required|array',
            'obat.*.id_obat' =>'required|integer', 
            'obat.*.jumlah_obat' => 'required|integer',
        ]);

        $savedData = [];
        foreach ($validateData['obat'] as $item) {
            $savedData[] = DetailTransaksiObat::create([
                'id_transaksi'   => $validateData['id_transaksi'],
                'id_obat'     => $item['id_obat'],
                'jumlah_obat' => $item['jumlah_obat'],
            ]);
        }

        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $savedData,
        ],201);

    }

    /**
     * Menampilkan Data Transaksi Obat tertentu.
     */
    public function show(String $id)
    {
        $data = DetailTransaksiObat::with('transaksi')->find($id);
        if(!$data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan', 
            ],404);
        }

        return response()->json([
            'succes' => true,
            'message' => 'Data ditemukan',
            'data' => $data,
        ],200);
    }

    /**
     * Update data Transaksi_layanan.
     */
    public function update(Request $request, String $id)
    {
        $data = DetailTransaksiObat::find($id);
        if(!$data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan!',
            ],404);
        }

        $validatedData = $request->validate([
            'id_obat' => 'required|integer',
            'id_transaksi' => 'required|integer',
            'jumlah_obat' => 'required|integer',
        ]);

        $data->update($validatedData);

        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil diperbaharui',
            'data' => $data,
        ]);
    }

    /**
     * Menghapus Data Transaksai Obat.
     */
    public function destroy(String $id)
    {
        $data = DetailTransaksiObat::find($id);
        if(!$data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan!',
            ],404);
        }
        $data->delete();
        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil dihapus!',
            'data' => $data,
        ]);
    }
}
