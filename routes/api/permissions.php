<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;

Route::group([
    'prefix' => 'permissions',
    'middleware' => 'auth:api',
], function () {
    Route::get('/', [PermissionController::class, 'index'])->name('permission.index')->middleware('permission:permission.index');
});
