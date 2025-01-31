<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Http;

Route::get('/provinces', function () {
    $response = Http::get('https://wilayah.id/api/provinces.json');
    return response()->json($response->json());
});

Route::get('/districts/{provinceCode}', function ($provinceCode) {
    $response = Http::get("https://wilayah.id/api/regencies/{$provinceCode}.json");
    return response()->json($response->json());
});

Route::get('/subdistricts/{districtCode}', function ($districtCode) {
    $response = Http::get("https://wilayah.id/api/districts/{$districtCode}.json");
    return response()->json($response->json());
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [RegisteredUserController::class, 'userInfo']);
    Route::post('/reports', [ReportsController::class, 'store']);
    Route::patch('/reports/{id}', [ReportsController::class, 'update']);   
    Route::get('/reports', [ReportsController::class, 'indexUser']);
    Route::get('/reports/{id}', [ReportsController::class, 'showUser']);
    Route::delete('/reports/{id}', [ReportsController::class, 'destroy']);
});

Route::middleware(['AdminMiddleware', 'auth:sanctum'])->group(function () {
    Route::get('/admin/reports', [ReportsController::class, 'index']);
    Route::post('/admin/reports/{id}/approve', [ReportsController::class, 'approve']);
    Route::post('/admin/reports/{id}/reject', [ReportsController::class, 'reject']);
    Route::get('/admin/reports/{id}', [ReportsController::class, 'show']);
});
