<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminEventController;

// Language Switching Route
Route::get('/locale/{lang}', function ($lang) {
    if (in_array($lang, ['en', 'fr'])) {
        session(['locale' => $lang]);
        app()->setLocale($lang);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
});
