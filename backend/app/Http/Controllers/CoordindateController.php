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
            $validated = $request->validated();
            $minDistance = 100; // 100 meters

            // 1. Get the last known location for this user
            $lastRecord = Coordindate::where('user_id', $validated['user_id'])
                ->latest()
                ->first();

            // 2. If a previous record exists, check if they've moved enough
            if ($lastRecord) {
                $distance = $this->calculateDistance(
                    (float)$validated['lat'],
                    (float)$validated['lon'],
                    (float)$lastRecord->lat,
                    (float)$lastRecord->lon
                );

                // IGNORE if the user is still within the 100m "bubble"
                if ($distance <= $minDistance) {
                    return response()->json([
                        'message' => 'User hasn\'t moved 100m yet. Record ignored.',
                        'distance_from_last' => round($distance, 2) . 'm'
                    ], 200);
                }
            }

            // 3. Save only if it's the first record OR they moved > 100m
            $coordinate = Coordindate::create($validated);

            return response()->json([
                'message' => 'Significant movement detected. Coordinate saved.',
                'data' => $coordinate
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
