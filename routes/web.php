<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\BostaWebhookController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/create-shipment', function () {
    return view('create-shipment');
});
Route::get('/track-shipment', function () {
    return view('track-shipment');
});
Route::post('/shipments', [ShipmentController::class, 'create']);
Route::post('/track', [ShipmentController::class, 'track']);
Route::post('/webhook/bosta', [BostaWebhookController::class, 'handle'])->withoutMiddleware(['csrf']);
