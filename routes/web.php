<?php

use App\Http\Controllers\DoEveryThing;
use Illuminate\Support\Facades\Route;

Route::get('/', [DoEveryThing::class, 'index'])->name('home');

// ->missing() covers a record that's already gone by the time the request
// arrives (a double-clicked delete, or two tabs racing each other) — bounce
// back to the app instead of a raw 404 page.
$backToHome = fn () => redirect()->route('home');

Route::post('/trans', [DoEveryThing::class, 'store_trans'])->name('trans.store');
Route::put('/trans/{tran}', [DoEveryThing::class, 'update_trans'])->name('trans.update')->missing($backToHome);
Route::delete('/trans/{tran}', [DoEveryThing::class, 'destroy_trans'])->name('trans.destroy')->missing($backToHome);

Route::post('/cats', [DoEveryThing::class, 'store_cat'])->name('cat.store');
Route::put('/cats/{cat}', [DoEveryThing::class, 'update_cat'])->name('cat.update')->missing($backToHome);
Route::delete('/cats/{cat}', [DoEveryThing::class, 'destroy_cat'])->name('cat.destroy')->missing($backToHome);
