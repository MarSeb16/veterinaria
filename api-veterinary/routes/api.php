<?php

use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\MedicalRecord\MedicalRecordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pets\PetController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Vaccination\VaccinationController;
use App\Http\Controllers\Veterinarie\VeterinarieController;

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

    Route::post("veterinaries/{id}", [VeterinarieController::class, "update"]);
    Route::get("veterinaries/config", [VeterinarieController::class, "config"]);
    Route::resource("veterinaries", VeterinarieController::class);

    Route::post("pets/{id}", [PetController::class, "update"]);
    Route::resource("pets", PetController::class);

    Route::get("appointments/search-pets/{search}", [AppointmentController::class, "searchPets"]);
    Route::post("appointments/filter-availability", [AppointmentController::class, "filter"]);
    Route::post("appointments/index", [AppointmentController::class, "index"]);
    Route::resource("appointments", AppointmentController::class);

    Route::get("/medical-records/calendar", [MedicalRecordController::class, "calendar"]);
    Route::put("/medical-records/update_aux/{id}", [MedicalRecordController::class, "update_aux"]);

    Route::post("vaccinations/index", [VaccinationController::class, "index"]);
    Route::resource("vaccinations", VaccinationController::class);
});
Route::get("appointment-excel", [AppointmentController::class, "downloadExcel"]);
