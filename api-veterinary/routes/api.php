<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Staff\StaffController;

Route::group([
    'prefix' => 'auth',
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
});

Route::group([
    'middleware' => ['auth:api']
], function ($router) {
    Route::resource("role", RoleController::class);
    Route::post("staffs/{id}", [StaffController::class, "update"]);
    Route::resource("staffs", StaffController::class);
});
