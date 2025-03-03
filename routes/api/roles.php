<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;

Route::group([
    'prefix' => 'roles',
    'middleware' => 'auth:api',
], function () {
    Route::get('/with-permissions', [RolePermissionController::class, 'index'])->name('roles.with-permissions')->middleware('permission:roles.with-permissions');
    Route::get('/with-permissions/{roleId}', [RolePermissionController::class, 'show'])->name('role.show')->middleware('permission:roles.with-permissions.show');
    Route::put('/with-permissions/{roleId}', [RolePermissionController::class, 'updateRolePermissions'])->name('roles.update-permissions')->middleware('permission:roles.update-permissions');
    Route::put('/{role}/users', [RoleController::class, 'updateUsers'])->name('roles.update-roles-users')->middleware('permission:roles.update-roles-users');
});
