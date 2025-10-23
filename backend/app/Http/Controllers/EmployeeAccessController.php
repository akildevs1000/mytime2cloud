<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmployeeAccessController extends Controller
{
    public function checkUserCode(Request $request)
    {
        $request->validate([
            'pin'                    => ['required', 'digits_between:1,10'], // only digits, length 1-10
            'employee_id'            => ['required', 'integer', 'exists:employees,id'],
            'device_id'              => ['required', 'string', 'max:50'],
            'is_multi_entry_allowed' => ['required', 'boolean'],
            'start_date'             => ['required', 'date', 'after_or_equal:today', 'before_or_equal:expiry_date'],
            'start_time'             => ['required', 'date_format:H:i'],
            'expiry_date'            => ['required', 'date', 'after_or_equal:start_date'],
            'expiry_time'            => ['required', 'date_format:H:i'],
        ]);

        $employee = Employee::find($request->employee_id);

        if (! $employee) {

            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        // ✅ Check if this pin exists for another employee in the same company
        $existing = Employee::where('company_id', $employee->company_id)
            ->where('rfid_card_password', $request->pin)
            ->where('id', '!=', $employee->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Pin already exists for another employee.',
            ], 409);
        }

        $payload = [
            'rfid_card_password'     => $request->pin,
            'is_multi_entry_allowed' => $request->is_multi_entry_allowed,
            'start_date'             => $request->start_date,
            'start_time'             => $request->start_time,
            'expiry_date'            => $request->expiry_date,
            'expiry_time'            => $request->expiry_time,
            'device_id'              => $request->device_id,
            'special_access'         => true,
        ];

        // info($payload);

        $employee->update($payload);

        // Combine dates and times into full Carbon instances
        $startDateTime  = Carbon::parse("{$payload['start_date']} {$payload['start_time']}");
        $expiryDateTime = Carbon::parse("{$payload['expiry_date']} {$payload['expiry_time']}");
        $now            = Carbon::now();

        // Use start date/time if it's greater than current time
        // If start date is greater than current date, use 1 year past date
        $expiryForDevice = $startDateTime->greaterThan($now)
            ? $now->copy()->subYear() // 1 year past date
            : $expiryDateTime;

        $url = env('SDK_URL') . "/" . $request->device_id . "/AddPerson";

        $data = [
            "userCode" => $employee->system_user_id,
            "name"     => "{$employee->first_name} {$employee->last_name}",
            "password" => $payload["rfid_card_password"],
            "expiry" => "{$payload['expiry_date']} {$payload['expiry_time']}:00", // 2023-01-01 00:00:00
            "expiry" => $expiryForDevice->format('Y-m-d H:i:s'),                  // 2023-01-01 00:00:00
        ];

        info($data);

        try {
            $response = Http::timeout(10)->post($url, $data);

            if ($response->successful()) {
                $json            = $response->json();
                $json["message"] = "Pin created successfully";
                return $json;
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
