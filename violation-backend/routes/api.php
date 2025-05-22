<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Violation;

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

Route::middleware('rate.limit')->get('/violations', function (Request $request) {
    $query = Violation::with('bisData'); // <- eager load BIS data

    if ($request->address) {
        $query->where(function ($q) use ($request) {
            $q->where('street_name', 'LIKE', '%' . $request->address . '%')
              ->orWhere('house_number', 'LIKE', '%' . $request->address . '%');
        });
    }

    if ($request->borough) {
        $query->where('borough', $request->borough);
    }

    if ($request->violation_type) {
        $query->where('violation_type', $request->violation_type);
    }

    return $query->paginate(10);
});
