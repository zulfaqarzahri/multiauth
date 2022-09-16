<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Models\User;
use App\Models\Admin;
use App\Http\Controllers\MailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('send-mail', [MailController::class, 'sendMail'])->name('sendMail');

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::prefix('user')->name('user.')->group(function(){
   Route::middleware(['guest:web', 'PreventBackHistory'])->group(function(){
       Route::view('login', 'dashboard.user.login')->name('login');
       Route::view('register', 'dashboard.user.register')->name('register');
       Route::post('create', [UserController::class, 'create'])->name('create');
       Route::post('check', [UserController::class, 'check'])->name('check');
       Route::get('verify', [UserController::class, 'verify'])->name('verify');
   });

    Route::middleware(['auth:web', 'is_user_verify_email', 'PreventBackHistory'])->group(function(){
        Route::view('home', 'dashboard.user.home')->name('home');
        Route::get('edit', [UserController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [UserController::class, 'update'])->name('update');
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
    });
});

Route::prefix('admin')->name('admin.')->group(function(){
   Route::middleware(['guest:admin', 'PreventBackHistory'])->group(function(){
       Route::view('login', 'dashboard.admin.login')->name('login');
       Route::post('check', [AdminController::class, 'check'])->name('check');

   });

    Route::middleware(['auth:admin', 'PreventBackHistory'])->group(function(){
        Route::get('home', [AdminController::class, 'home'])->name('home');
        Route::get('edit-user/{id}', [AdminController::class, 'edit'])->name('edit-user');
        Route::post('update-user/{id}', [AdminController::class, 'update'])->name('update-user');
        Route::post('delete-user', [AdminController::class, 'delete'])->name('delete-user');
        Route::post('logout', [AdminController::class, 'logout'])->name('logout');

    });
});

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
