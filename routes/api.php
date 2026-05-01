<?php

use App\Http\Controllers\AntrianPembayaranController;
use App\Http\Controllers\DetailTransaksiController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//API Transaksi
Route::apiResource('transaksi',TransaksiController::class)
->parameters(['transaksi' => 'id_transaksi']);

//API Layanan
Route::get('/layanan', LayananController::class . '@index');
Route::post('/layanan', LayananController::class . '@store');
Route::get('/layanan/{id_layanan}', LayananController::class . '@show');
Route::put('/layanan/{id_layanan}', LayananController::class . '@update');
Route::delete('/layanan/{id_layanan}', LayananController::class . '@destroy');

//API Pembayaran
Route::get('/pembayaran', [PembayaranController::class, 'index']);
Route::post('/pembayaran', [PembayaranController::class, 'store']);
Route::get('/pembayaran/{id_pembayaran}', [PembayaranController::class, 'show']);
Route::put('/pembayaran/{id_pembayaran}', [PembayaranController::class, 'update']);
Route::delete('/pembayaran/{id_pembayaran}', [PembayaranController::class, 'destroy']);

//API Antrian_Pembayaran
Route::get('/antrian_pembayaran', [AntrianPembayaranController::class, 'index']);
Route::post('/antrian_pembayaran', [AntrianPembayaranController::class, 'store']);
Route::get('/antrian_pembayaran/{id_antrian_pay}', [AntrianPembayaranController::class, 'show']);
Route::put('/antrian_pembayaran/{id_antrian_pay}', [AntrianPembayaranController::class, 'update']);
Route::delete('/antrian_pembayaran/{id_antrian_pay}', [AntrianPembayaranController::class, 'destroy']);

//API Detail_Transaksi
Route::get('/detail_transaksi', [DetailTransaksiController::class, 'index']);
Route::post('/detail_transaksi', [DetailTransaksiController::class, 'store']);
Route::get('/detail_transaksi/{id_detail}', [DetailTransaksiController::class, 'show']);
Route::put('/detail_transaksi/{id_detail}', [DetailTransaksiController::class, 'update']);
Route::delete('/detail_transaksi/{id_detail}', [DetailTransaksiController::class, 'destroy']);