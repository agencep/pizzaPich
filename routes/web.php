<?php

use App\Livewire\AuthLogin;
use App\Livewire\KitchenView;
use App\Livewire\PosPizza;
use Illuminate\Support\Facades\Route;

Route::get('login', AuthLogin::class)->name('login');

Route::get('/', PosPizza::class)->name('home');
Route::get('pos', PosPizza::class)->name('pos');
Route::get('pizzaiolo', KitchenView::class)->name('kitchen');
