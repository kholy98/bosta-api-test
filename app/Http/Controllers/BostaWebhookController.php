<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BostaWebhookController extends Controller
{
    private const STATE_MAPPING = [
        10 => 'pickup_requested',
        11 => 'waiting_for_route',
        20 => 'route_assigned',
        21 => 'picked_up_from_business',
        22 => 'picking_up_from_consignee',
        23 => 'picked_up_from_consignee',
        24 => 'received_at_warehouse',
        25 => 'fulfilled',
        30 => 'in_transit',
        40 => 'picking_up',
        41 => 'picked_up',
        45 => 'delivered',
        46 => 'returned_to_business',
        47 => 'exception',
        48 => 'terminated',
        49 => 'canceled',
        100 => 'lost',
        101 => 'damaged',
        102 => 'investigation',
        103 => 'awaiting_your_action',
        104 => 'archived',
        105 => 'on_hold',
        60 => 'returned_to_stock',
    ];

    public function handle(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('Bosta Webhook Received', [
            'ip' => $request->ip(),
            'payload' => $request->all(),
        ]);

        // Optional: Verify Bosta IP addresses (get from Bosta support)
        // $allowedIps = ['Bosta IPs here'];
        // if (!in_array($request->ip(), $allowedIps)) {
        //     Log::warning('Unauthorized webhook attempt from IP: ' . $request->ip());
        //     abort(403);
        // }

        $data = $request->all();

        // Validate required fields
        if (! isset($data['trackingNumber']) || ! isset($data['state'])) {
            Log::error('Invalid webhook payload', ['payload' => $data]);

            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $trackingNumber = $data['trackingNumber'];
        $stateCode = (int) $data['state'];

        // Map state code to status string
        $status = self::STATE_MAPPING[$stateCode] ?? 'unknown';

        // Prepare update data
        $updateData = [
            'status' => $status,
            'state_code' => $stateCode,
        ];

        // Add optional fields if present
        if (isset($data['_id'])) {
            $updateData['bosta_id'] = $data['_id'];
        }
        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }
        if (isset($data['cod'])) {
            $updateData['cod'] = $data['cod'];
        }
        if (isset($data['timeStamp'])) {
            $updateData['state_changed_at'] = date('Y-m-d H:i:s', $data['timeStamp'] / 1000);
        }
        if (isset($data['isConfirmedDelivery'])) {
            $updateData['is_confirmed_delivery'] = $data['isConfirmedDelivery'];
        }
        if (isset($data['deliveryPromiseDate'])) {
            $updateData['delivery_promise_date'] = date('Y-m-d', strtotime($data['deliveryPromiseDate']));
        }
        if (isset($data['exceptionReason'])) {
            $updateData['exception_reason'] = $data['exceptionReason'];
        }
        if (isset($data['exceptionCode'])) {
            $updateData['exception_code'] = $data['exceptionCode'];
        }
        if (isset($data['businessReference'])) {
            $updateData['business_reference'] = $data['businessReference'];
        }
        if (isset($data['numberOfAttempts'])) {
            $updateData['number_of_attempts'] = $data['numberOfAttempts'];
        }

        // Update the order
        $order = Order::where('tracking_number', $trackingNumber)->first();
        if ($order) {
            $order->update($updateData);
            Log::info('Order updated via webhook', [
                'tracking_number' => $trackingNumber,
                'new_status' => $status,
                'state_code' => $stateCode,
            ]);
        } else {
            Log::warning('Order not found for webhook update', [
                'tracking_number' => $trackingNumber,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
