<?php

use App\Http\Controllers\OperatorController;
use App\Http\Controllers\QueueController;
use App\Http\Middleware\LocalOperator;
use Illuminate\Support\Facades\Route;

Route::get('/', [QueueController::class, 'index'])->name('queue.index');
Route::post('/antrean', [QueueController::class, 'take'])->middleware('throttle:60,1')->name('queue.take');
Route::get('/tampilan', [QueueController::class, 'display'])->name('queue.display');
Route::get('/antrean/status', [QueueController::class, 'status'])->withoutMiddleware('web')->name('queue.status');
Route::prefix('operator')->middleware(LocalOperator::class)->group(function (): void {
    Route::get('/', [OperatorController::class, 'index'])->name('operator.index');
    Route::post('/berikutnya', [OperatorController::class, 'next'])->name('operator.next');
    Route::post('/{ticket}/selesai', [OperatorController::class, 'complete'])->name('operator.complete');
    Route::post('/{ticket}/lewati', [OperatorController::class, 'skip'])->name('operator.skip');
});
