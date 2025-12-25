<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\BostaApiService;
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
            'message' => 'Shipment created successfully',
        ]);
    }

    public function track(Request $request, BostaApiService $bosta)
    {
        $tracking_number = $request->route('tracking_number') ?? $request->input('tracking_number');

        try {
            $response = $bosta->getDelivery($tracking_number);

            // Update DB if needed
            $order = Order::where('tracking_number', $tracking_number)->first();
            if ($order) {
                $order->update(['status' => $response['data']['state']['value']]);
            }

            $data = $response['data'];
        } catch (\Exception $e) {
            // Fallback to DB data if API fails
            $order = Order::where('tracking_number', $tracking_number)->first();
            if ($order) {
                $data = [
                    'trackingNumber' => $order->tracking_number,
                    'state' => [
                        'status' => $order->status,
                        'value' => $order->state_code,
                        'timestamp' => $order->state_changed_at?->toISOString(),
                    ],
                ];
            } else {
                abort(404, 'Tracking number not found');
            }
        }

        if ($request->is('api/*')) {
            return response()->json(['data' => $data]);
        } else {
            return view('track-result', ['data' => $data]);
        }
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
            'data' => $response,
        ]);
    }

    public function createPickup(Request $request, BostaApiService $bosta)
    {
        $request->validate([
            'scheduledDate' => 'required|date|after:today',
            'businessLocationId' => 'nullable|string',
            'contactPerson.name' => 'required|string',
            'contactPerson.phone' => 'required|string',
            'contactPerson.secPhone' => 'nullable|string',
            'contactPerson.email' => 'nullable|email',
            'notes' => 'nullable|string',
            'noOfPackages' => 'required|integer|min:1',
            'packageType' => 'nullable|string|in:Normal,Light Bulky,Heavy Bulky',
            'repeatedData.repeatedType' => 'nullable|string|in:One Time,Daily,Weekly',
            'repeatedData.days' => 'nullable|array',
            'repeatedData.days.*' => 'string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'repeatedData.startDate' => 'nullable|date',
            'repeatedData.endDate' => 'nullable|date|after:repeatedData.startDate',
        ]);

        // Prepare payload
        $payload = [
            'scheduledDate' => $request->scheduledDate,
            'contactPerson' => [
                'name' => $request->input('contactPerson.name'),
                'phone' => $request->input('contactPerson.phone'),
            ],
            'noOfPackages' => $request->noOfPackages,
        ];

        if ($request->businessLocationId) {
            $payload['businessLocationId'] = $request->businessLocationId;
        }

        if ($request->input('contactPerson.secPhone')) {
            $payload['contactPerson']['secPhone'] = $request->input('contactPerson.secPhone');
        }

        if ($request->input('contactPerson.email')) {
            $payload['contactPerson']['email'] = $request->input('contactPerson.email');
        }

        if ($request->notes) {
            $payload['notes'] = $request->notes;
        }

        if ($request->packageType) {
            $payload['packageType'] = $request->packageType;
        } else {
            $payload['packageType'] = 'Normal';
        }

        if ($request->input('repeatedData.repeatedType')) {
            $payload['repeatedData'] = [
                'repeatedType' => $request->input('repeatedData.repeatedType'),
            ];

            if ($request->input('repeatedData.days')) {
                $payload['repeatedData']['days'] = $request->input('repeatedData.days');
            }

            if ($request->input('repeatedData.startDate')) {
                $payload['repeatedData']['startDate'] = $request->input('repeatedData.startDate');
            }

            if ($request->input('repeatedData.endDate')) {
                $payload['repeatedData']['endDate'] = $request->input('repeatedData.endDate');
            }
        }

        // API Call
        $response = $bosta->createPickup($payload);

        return response()->json([
            'success' => true,
            'message' => 'Pickup created successfully',
            'data' => $response,
        ]);
    }
}
