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
            // 1. Ensure only validated data is used
            $coordinate = Coordindate::create($request->validated());

            // 2. Return a 201 Created response with the resource
            return response()->json([
                'message' => 'Coordinate recorded successfully.',
                'data' => $coordinate
            ], 201);
        } catch (\Exception $e) {
            // 3. Handle unexpected database or system errors
            return response()->json([
                'message' => 'Failed to save coordinate.',
                'error' => $e->getMessage() // Consider hiding this in production
            ], 500);
        }
    }
}
