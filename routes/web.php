<?php

use App\Http\Controllers\BostaWebhookController;
use App\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

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
