<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class BostaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify Bosta IP (optional, from docs)
        $allowedIps = ['Bosta IPs here'];  // Get from Bosta support
        if (!in_array($request->ip(), $allowedIps)) {
            abort(403);
        }

        $data = $request->json()->all();
        // Payload from docs: {"actionType": "ItemsStatusChanged", "data": {"itemId": "...", "itemStatus": "DELIVERED"}}

        $trackingNumber = $data['data']['itemId'];
        $newStatus = $data['data']['itemStatus'];

        // Update DB
        Order::where('tracking_number', $trackingNumber)->update(['status' => strtolower($newStatus)]);

        return response()->json(['status' => 'ok']);
    }
}
