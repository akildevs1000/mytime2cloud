<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\AITrigger;

class AITriggerController extends Controller
{
    public function index(AITrigger $AITrigger, Request $request)
    {
        return $AITrigger->orderBy("id", "desc")->paginate(500);
    }


    public function store(Request $request)
    {
        $message = $request->description;
        $companyId = $request->company_id ?? 2;

        $trigger = AITrigger::createFromMessage($message, $companyId);

        if (!$trigger) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Trigger already exists or message invalid/unrelated.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'trigger' => $trigger
        ]);
    }

    public function destroy($id)
    {
        AITrigger::findOrFail($id)->delete();

        return response()->json([
            "message" => "Deleted"
        ]);
    }
}
