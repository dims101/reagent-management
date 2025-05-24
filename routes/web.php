<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\ShowStock;
use App\Livewire\CreateStock;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/stock', ShowStock::class)->name('self-stock');
Route::get('/stock/create', CreateStock::class)->name('create-stock');
Route::get('/register',Register::class)->name('register');
Route::get('/login',[AuthController::class,'showLogin'])->name('login');
