<?php

use App\Http\Controllers\ShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/shipments', [ShipmentController::class, 'create']);
Route::get('/shipments/{tracking_number}', [ShipmentController::class, 'track']);
Route::put('/shipments/{tracking_number}', [ShipmentController::class, 'update']);
Route::post('/pickups', [ShipmentController::class, 'createPickup']);
Route::post('/webhook/bosta', [App\Http\Controllers\BostaWebhookController::class, 'handle']);
