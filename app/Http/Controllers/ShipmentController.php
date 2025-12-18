<?php

namespace App\Http\Controllers;

use App\Services\BostaApiService;
use App\Models\Order;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function create(Request $request, BostaApiService $bosta)
    {
        $request->validate([
            'receiver_first_name' => 'required',
            'receiver_last_name' => 'required',
            'receiver_phone' => 'required',
            'receiver_email' => 'nullable|email',
            'building_number' => 'required|integer',
            'floor' => 'nullable',
            'apartment' => 'nullable',
            'first_line' => 'required',
            'city' => 'required',
            'zone' => 'required',
            'notes' => 'nullable',
            'cod' => 'required|numeric|min:0',
            'business_reference' => 'nullable',
        ]);

        // Prepare payload (order: type, dropOffAddress, receiver, notes, cod)
        $payload = [
            'type' => 10,
            'dropOffAddress' => [
                'buildingNumber' => $request->building_number,
                'firstLine' => $request->first_line,
                'city' => $request->city,
                'zone' => $request->zone,
            ],
            'receiver' => [
                'firstName' => $request->receiver_first_name,
                'lastName' => $request->receiver_last_name,
                'phone' => $request->receiver_phone,
            ],
            'notes' => $request->notes ?: '',
            'cod' => $request->cod,
        ];
        if ($request->floor) {
            $payload['dropOffAddress']['floor'] = $request->floor;
        }
        if ($request->apartment) {
            $payload['dropOffAddress']['apartment'] = $request->apartment;
        }
        if ($request->receiver_email) {
            $payload['receiver']['email'] = $request->receiver_email;
        }
        if ($request->business_reference) {
            $payload['businessReference'] = $request->business_reference;
        }

        // API Call
        $response = $bosta->createDelivery($payload);


        // Save to DB
        Order::create([
            'tracking_number' => $response['data']['trackingNumber'],
            'status' => 'created',
        ]);

        return response()->json([
            'success' => true,
            'tracking_number' => $response['data']['trackingNumber'],
            'message' => 'Shipment created successfully'
        ]);
    }

    public function track($tracking_number, BostaApiService $bosta)
    {
        $response = $bosta->getDelivery($tracking_number);

        // Update DB if needed
        $order = Order::where('tracking_number', $tracking_number)->first();
        if ($order) {
            $order->update(['status' => $response['data']['state']['value']]);
        }

        return response()->json($response);
    }

    public function update(Request $request, $tracking_number, BostaApiService $bosta)
    {
        $request->validate([
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
            // Add other updatable fields as needed
        ]);

        $payload = [];
        if ($request->status) {
            $payload['status'] = $request->status;
        }
        if ($request->notes) {
            $payload['notes'] = $request->notes;
        }

        $response = $bosta->updateDelivery($tracking_number, $payload);

        // Update DB if needed
        $order = Order::where('tracking_number', $tracking_number)->first();
        if ($order && isset($payload['status'])) {
            $order->update(['status' => $payload['status']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shipment updated successfully',
            'data' => $response
        ]);
    }
}
