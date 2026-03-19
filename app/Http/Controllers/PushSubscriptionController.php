<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required',
            'keys.auth' => 'required',
            'keys.p256dh' => 'required',
        ]);

        $endpoint = $request->endpoint;
        $key = $request->keys['p256dh'];
        $token = $request->keys['auth'];
        
        $user = auth()->user();

        if ($user) {
            $user->updatePushSubscription($endpoint, $key, $token);
            return response()->json(['message' => 'Subscription successful'], 201);
        }

        return response()->json(['message' => 'User not authenticated'], 401);
    }
}
