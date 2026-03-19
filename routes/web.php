<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\PosPizza;
use App\Livewire\KitchenView;

Route::get('/', PosPizza::class)->name('home');
Route::get('pos', PosPizza::class)->name('pos');
Route::get('pizzaiolo', KitchenView::class)->name('kitchen');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
