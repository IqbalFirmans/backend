<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('authentication')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('forms')->group(function () {
            Route::post('', [FormController::class, 'request']);
            Route::get('', [FormController::class, 'getAll']);
            Route::get('mine', [FormController::class, 'getMyform']);
            Route::get('{id}', [FormController::class, 'show']);
        });

        Route::prefix('questions')->group(function() {
            Route::get('{id}', [QuestionController::class, 'show']);
        });
        Route::prefix('response')->group(function() {
            Route::get('', [ResponseController::class, 'get_response']);
            Route::post('', [ResponseController::class, 'response']);
        });
    });
});
