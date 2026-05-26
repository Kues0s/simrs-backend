<?php

use App\Http\Controllers\AntrianPembayaranController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//API Transaksi
Route::get('/transaksi/jumlah-transaksi', [TransaksiController::class, 'jumlahTransaksi']);
Route::get('/transaksi', [TransaksiController::class, 'index']);
Route::post('/transaksi', [TransaksiController::class, 'store']);
Route::get('/transaksi/{id_transaksi}', [TransaksiController::class, 'show']);
Route::put('/transaksi/{id_transaksi}', [TransaksiController::class, 'update']);
Route::delete('/transaksi/{id_transaksi}', [TransaksiController::class, 'destroy']);
Route::get('/transaksi/{id_antrian}/antrian', [TransaksiController::class, 'getTransaksiByIdAntrian']);


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
