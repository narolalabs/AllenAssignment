<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Violation;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ViolationController;

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

Route::middleware('rate.limit')->get('/violations', [ViolationController::class, 'getViolations']);
