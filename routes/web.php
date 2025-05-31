<?php

use App\Livewire\Dashboard;
use App\Livewire\ShowStock;
use App\Livewire\Auth\Login;
use App\Livewire\CreateStock;
use App\Livewire\ApprovalList;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/stock', ShowStock::class)->name('self-stock');
Route::get('/stock/create', CreateStock::class)->name('create-stock');
Route::get('/register', Register::class)->name('register');
Route::get('/approval', ApprovalList::class)->name('approval-list');
// Route::get('/login',[AuthController::class,'showLogin'])->name('login');

Auth::routes($options = [
    'register' => false, // Disable default registration route
    'login' => false, // Disable default password reset routes
]);

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/testmail', function () {
    $department = \App\Models\Department::find(1);
    $name = $department->name;
    Mail::to('uztadz.jablinx@gmail.com')->send(new \App\Mail\SendApproval($name));
    return [$department->name, $department->pic_id, $department->manager_id];
});
