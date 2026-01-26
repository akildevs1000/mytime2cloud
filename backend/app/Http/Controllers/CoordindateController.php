<?php

namespace App\Http\Controllers;

use App\Http\Requests\Coordindate\StoreRequest;
use App\Models\Coordindate;

class CoordindateController extends Controller
{
    public function index()
    {
        return Coordindate::where("company_id", request("company_id"))
            ->when(request("user_id"), function ($query, $userId) {
                $query->where("user_id", $userId);
            })
            ->orderBy("id", "desc")
            ->paginate(request("per_page", 15));
    }

    public function store(StoreRequest $request)
    {
        try {
            $data = $request->validated();

            // 1. Find the most recent coordinate for this user
            $lastCoord = Coordindate::where('user_id', $data['user_id'])
                ->latest()
                ->first();

            // 2. Check for duplicate coordinates
            if (
                $lastCoord &&
                (string)$lastCoord->lat === (string)$data['lat'] &&
                (string)$lastCoord->lon === (string)$data['lon']
            ) {

                // 📝 Log the duplicate event
                Log::info("Duplicate location skipped for User ID: {$data['user_id']}", [
                    'lat' => $data['lat'],
                    'lon' => $data['lon']
                ]);

                return response()->json([
                    'message' => 'Duplicate coordinate. No save required.'
                ], 200);
            }

            // 3. Create new record if unique
            $coordinate = Coordindate::create($data);

            return response()->json([
                'message' => 'Coordinate recorded successfully.',
                'data' => $coordinate
            ], 201);
        } catch (\Exception $e) {
            // 📝 Log the error if something fails
            Log::error("Failed to save coordinate for User ID: {$request->user_id}", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to save coordinate.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }
}
