<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $layanan = Layanan::all();
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diambil',
                'data' => $layanan,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data layanan',
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
            'nama_layanan' => 'required|string',
            'kategori' => 'required|string',
            'harga' => 'required|numeric',
            'status_layanan' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $layanan = Layanan::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil dibuat',
                'data' => $layanan,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id_layanan)
    {
        try{
            $layanan = Layanan::findOrFail($id_layanan);
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diambil',
                'data' => $layanan, 
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengambil data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Layanan $id_layanan)
    {
        try{
            $validatedData = $request->validate([
                'nama_layanan' => 'sometimes|required|string',
                'kategori' => 'sometimes|required|string',
                'harga' => 'sometimes|required|numeric',
                'status_layanan' => 'sometimes|required|in:aktif,nonaktif',
            ]);
            $id_layanan->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diupdate',
                'data' => $id_layanan,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal mengupdate data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Layanan $id_layanan)
    {
        try{
            $id_layanan->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil dihapus',
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Gagal menghapus data layanan',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
