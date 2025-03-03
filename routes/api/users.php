<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

Route::group([
    'prefix' => 'users',
    'middleware' => 'auth:api',
], function () {
    Route::get('/', [UsersController::class, 'index'])->name('users.index')->middleware('permission:users.index');
    Route::get('/without-roles', [UsersController::class, 'getUsersWithoutRoles'])->name('users.without-roles')->middleware('permission:users.without-roles');
    Route::get('/with-locations', [UsersController::class, 'getAllWithLocations'])->name('users.with-locations')->middleware('permission:users.with-locations');
    // Rutas nuevas:
    Route::get('/{id}', [UsersController::class, 'show'])
        ->name('users.show')
        ->middleware('permission:users.show');

    Route::put('/{id}', [UsersController::class, 'update'])
        ->name('users.update')
        ->middleware('permission:users.update');

    Route::delete('/{id}', [UsersController::class, 'destroy'])
        ->name('users.destroy')
        ->middleware('permission:users.destroy');

    Route::post('/', [UsersController::class, 'store'])
        ->name('users.store')
        ->middleware('permission:users.store');
});
