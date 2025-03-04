<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

Route::group([
    'prefix' => 'users',
    'middleware' => ['auth:api', 'check_route_permission'],
], function () {
    Route::get('/', [UsersController::class, 'index'])->name('users.index');
    Route::get('/without-roles', [UsersController::class, 'getUsersWithoutRoles'])->name('users.without-roles');
    Route::get('/with-locations', [UsersController::class, 'getAllWithLocations'])->name('users.with-locations');
    // Rutas nuevas:
    Route::get('/{id}', [UsersController::class, 'show'])
        ->name('users.show');

    Route::put('/{id}', [UsersController::class, 'update'])
        ->name('users.update');

    Route::delete('/{id}', [UsersController::class, 'destroy'])
        ->name('users.destroy');

    Route::post('/', [UsersController::class, 'store'])
        ->name('users.store');
});
