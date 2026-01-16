<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminEventController;

Route::redirect('/', '/admin/events')->name('home');
// Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show'); // Removed public show route

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
});
