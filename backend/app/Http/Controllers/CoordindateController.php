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
                ->latest() // Shortcut for orderBy('created_at', 'desc')
                ->first();

            // 2. Check if the values match the last record
            // We cast to string to ensure the comparison is consistent
            if (
                $lastCoord &&
                (string)$lastCoord->lat === (string)$data['lat'] &&
                (string)$lastCoord->lon === (string)$data['lon']
            ) {

                return response()->json([
                    'message' => 'Duplicate coordinate. No save required.'
                ], 200);
            }

            // 3. Create new record if it's different
            $coordinate = Coordindate::create($data);

            return response()->json([
                'message' => 'Coordinate recorded successfully.',
                'data' => $coordinate
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save coordinate.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }
}
