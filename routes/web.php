<?php

use App\Livewire\Reject;
use App\Livewire\History;
use App\Livewire\TestPage;
use App\Models\Department;
use App\Livewire\Dashboard;
use App\Livewire\ShowStock;
use App\Livewire\Auth\Login;
use App\Livewire\ShowTicket;
use App\Livewire\CreateStock;
use App\Livewire\ApprovalList;
use App\Livewire\AssignTicket;
use App\Livewire\CreateTicket;
use App\Livewire\Auth\Register;
use App\Livewire\ShowOtherStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Livewire\Auth\ForgotPassword;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/stock', ShowStock::class)->name('self-stock');
Route::get('/stock/others', ShowOtherStock::class)->name('other-stock');
Route::get('/stock/create', CreateStock::class)->name('create-stock');
Route::get('/register', Register::class)->name('register');
Route::get('/approval', ApprovalList::class)->name('approval-list');
Route::get('/reject', Reject::class)->name('reject');
Route::get('/history', History::class)->name('history');
Route::get('/ticket/create', CreateTicket::class)->name('create-ticket');
Route::get('/ticket/assign', AssignTicket::class)->name('assign-ticket');
Route::get('/ticket', ShowTicket::class)->name('show-ticket');
// Route::get('/login',[AuthController::class,'showLogin'])->name('login');

Auth::routes($options = [
    'register' => false, // Disable default registration route
    'login' => false, // Disable default password reset routes
]);

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
});
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



Route::get('/testmail', function () {
    $department = \App\Models\Department::find(1);
    $name = $department->name;
    Mail::to('uztadz.jablinx@gmail.com')->send(new \App\Mail\SendApprovalmanager($name, 'https://www.google .com/'));
    return [$department->name, $department->pic_id, $department->manager_id];
});

Route::get('/test', TestPage::class)->name('test-page');
