<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', [AuthController::class, 'index'])->name('index');
Route::get('/dont-have-access', [AuthController::class, 'denied'])->name('dont-have-access');
Route::post('/registration', [AuthController::class, 'registration']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/complete-profile/{id}', [UserController::class, 'completeProfile']);
    Route::post('/user/{id}/transaction-tax', [UserController::class, 'transactionTax']);
    Route::post('/users/edit-profile/{id}', [UserController::class, 'editProfile']);
    Route::get('/logout/{id}', [UserController::class, 'logoutUser']);
});
