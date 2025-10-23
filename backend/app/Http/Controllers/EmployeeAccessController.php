<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmployeeAccessController extends Controller
{
    public function checkUserCode(Request $request)
    {
        $pin             = $request->query('pin');
        $system_user_id  = $request->query('system_user_id');
        $name            = $request->query('name');
        $companyId       = $request->query('company_id');
        $deviceId = $request->query('device_id');
        $expiry = $request->query('expiry');
        $isMultiEntry    = $request->query('isMultiEntry', true);

        if (! $pin || ! $companyId || ! $deviceId || ! $name || ! $system_user_id || ! $expiry) {
            return response()->json([
                'success' => false,
                'message' => 'pin,system_user_id,name,company_id, and device_id query parameters are required.',
            ], 400);
        }

        $url = "http://192.168.2.56:8080/{$deviceId}/AddPerson";

        $data = [
            "userCode"     => $system_user_id,
            "name"         => $name,
            "password"     => $pin,
            "expiry"       => $expiry,
        ];

        try {
            $response = Http::timeout(10)->post($url, $data);

            if ($response->successful()) {
                // Optionally, save employee locally

                // Employee::where('company_id', $companyId)->where('system_user_id', $system_user_id)
                //     ->update(["rfid_card_password" => $pin]);

                return response()->json([
                    'success' => true,
                    'exists'  => false,
                    'message' => 'Pin created successfully.',
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create pin. Device responded with error.',
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create pin. Exception occurred.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
