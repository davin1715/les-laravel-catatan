<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatatanController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\HaloController;
use App\Http\Controllers\AboutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Utama / Beranda
Route::get('/', [BerandaController::class, 'index'])->name('beranda');

// Rute Navigasi Lainnya
Route::get('/halo', [HaloController::class, 'index'])->name('halo');
Route::get('/about', [AboutController::class, 'index'])->name('tentang');

// --- RUTE CATATAN ---
Route::middleware(['auth'])->group(function () {
    Route::get('/catatan', [CatatanController::class, 'index']);
    Route::get('/catatan/tambah', [CatatanController::class, 'create']);
    Route::post('/catatan', [CatatanController::class, 'store']);
    Route::get('/catatan/arsip', [CatatanController::class, 'arsip']);
    Route::get('/catatan/export', [CatatanController::class, 'export']);
    Route::get('/catatan/import', [CatatanController::class, 'importForm']);
    Route::post('/catatan/import', [CatatanController::class, 'import']);
    Route::get('/catatan/{id}', [CatatanController::class, 'show']);
    Route::get('/catatan/{id}/edit', [CatatanController::class, 'edit']);
    Route::put('/catatan/{id}', [CatatanController::class, 'update']);
    Route::delete('/catatan/{id}', [CatatanController::class, 'destroy']);
    Route::post('/catatan/{id}/archive', [CatatanController::class, 'archive']);
    Route::post('/catatan/{id}/unarchive', [CatatanController::class, 'unarchive']);
});

// --- RUTE KONTAK ---
Route::get('/kontak', [KontakController::class, 'index']);
Route::get('/kontak/tambah', [KontakController::class, 'create']);
Route::post('/kontak', [KontakController::class, 'store']);
Route::get('/kontak/{id}', [KontakController::class, 'show']);
Route::get('/kontak/{id}/edit', [KontakController::class, 'edit']);
Route::put('/kontak/{id}', [KontakController::class, 'update']);
Route::delete('/kontak/{id}', [KontakController::class, 'destroy']);

// --- PENGALIHAN DASHBOARD KE /CATATAN ---
Route::get('/dashboard', function () {
    return redirect('/catatan');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- RUTE PROFILE & AUTH ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::get('/catatan', [App\Http\Controllers\CatatanController::class, 'index'])->name('catatan')->middleware('auth');
