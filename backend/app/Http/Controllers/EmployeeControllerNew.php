<?php

namespace App\Http\Controllers;

use App\Models\BankInfo;
use App\Models\DocumentInfo;
use App\Models\EmiratesInfo;
use App\Models\Employee;
use App\Models\Passport;
use App\Models\Qualification;
use App\Models\User;
use App\Models\Visa;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Import Str facade for helper functions

class EmployeeControllerNew extends Controller
{

    public function storeNew(Request $request)
    {
        $employee = [
            "employee_id" => $request->employee_id,
            "company_id"  => $request->company_id,
        ];

        $employeeDevice = [
            "system_user_id" => $request->system_user_id,
            "company_id"     => $request->company_id,
        ];

        $controller = new Controller;
        try {
            // 1. Validate the incoming request data
            $validatedData = $request->validate([
                'title'                => 'nullable|string|max:10',
                'first_name'           => 'required|string|max:100',
                'last_name'            => 'required|string|max:100',
                'full_name'            => 'required|string|max:255',
                'display_name'         => 'nullable|string|max:255',
                'employee_id'          => ['required', 'max:6', $controller->uniqueRecord("employees", $employee)],
                'system_user_id'       => ['required', 'digits_between:1,6', 'numeric', $controller->uniqueRecord('employees', $employeeDevice)],
                'branch_id'            => 'required|integer',                       // Assumes a 'branches' table
                'department_id'        => 'required|integer|exists:departments,id', // Assumes a 'departments' table
                'joining_date'         => 'required|date',
                'phone_number'         => 'nullable|string|max:20',
                'whatsapp_number'      => 'nullable|string|max:20',
                'company_id'           => 'required|integer', // Assumes a 'branches' table
                'profile_image_base64' => 'nullable|string',  // Con
            ]);

            $validatedData["joining_date"] = date("Y-m-d", strtotime($validatedData["joining_date"]));

            $dataToStore = $validatedData;
            $imagePath   = null;

            // Handle Base64 Image Decoding and Storage
            if (! empty($validatedData['profile_image_base64'])) {

                $base64Image = $validatedData['profile_image_base64'];

                // 1. Separate the file data from the MIME type prefix (e.g., 'data:image/png;base64,')
                if (Str::startsWith($base64Image, 'data:')) {
                    list($type, $base64Image) = explode(';', $base64Image);
                    list(, $base64Image)      = explode(',', $base64Image);
                }

                // 2. Decode the Base64 string into binary data
                $imageData = base64_decode($base64Image);

                // 3. Determine the file extension (simple approach, refine if needed)
                // We'll assume PNG or JPG for simplicity, or try to extract from MIME type.
                // A safer way is to infer the extension, but using a default works for now.
                $ext = '.png'; // Default extension
                if (isset($type) && str_contains($type, 'jpeg')) {
                    $ext = '.jpg';
                }

                // 4. Create the unique file name, similar to your old way
                $fileName = time() . '_' . Str::random(10) . $ext;

                // 5. Define the target directory path
                $targetDir = public_path('media/employee/profile_picture/');

                // Ensure the directory exists
                if (! is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                // 6. Save the binary data to the file path
                $imagePath = $targetDir . $fileName;
                file_put_contents($imagePath, $imageData);

                // 7. Store the file name in the data array for the database
                $dataToStore['profile_picture'] = $fileName;
            }

            // Remove the Base64 string before creating the record
            unset($dataToStore['profile_image_base64']);

            // 8. Create the Employee record
            $employee = Employee::create($dataToStore);

            // 9. Return a successful response
            return response()->json([
                'message'  => 'Employee created successfully!',
                'employee' => $employee,
            ], 201);
        } catch (ValidationException $e) {

            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateNew(Request $request, $id)
    {
        try {
            // 1. Find employee
            $employee = Employee::findOrFail($id);

            // 2. Validate incoming data
            $validatedData = $request->validate([
                'title'                => 'nullable|string|max:10',
                'first_name'           => 'required|string|max:100',
                'last_name'            => 'required|string|max:100',
                'full_name'            => 'required|string|max:255',
                'display_name'         => 'nullable|string|max:255',
                'employee_id'          => 'required|string|max:6|unique:employees,employee_id,' . $employee->id,
                'system_user_id'       => 'required|string|max:6|unique:employees,system_user_id,' . $employee->id,
                'branch_id'            => 'required|integer',                       // Assumes a 'branches' table
                'department_id'        => 'required|integer|exists:departments,id', // Assumes a 'departments' table
                'joining_date'         => 'required|date',
                'phone_number'         => 'nullable|string|max:20',
                'whatsapp_number'      => 'nullable|string|max:20',
                'profile_image_base64' => 'nullable|string', // Con
            ]);

            $validatedData["joining_date"] = date("Y-m-d", strtotime($validatedData["joining_date"]));

            $dataToUpdate = $validatedData;
            $imagePath    = null;

            // 3. Handle Base64 Image if provided
            if (! empty($validatedData['profile_image_base64'])) {
                $base64Image = $validatedData['profile_image_base64'];

                // Separate MIME and data
                if (Str::startsWith($base64Image, 'data:')) {
                    list($type, $base64Image) = explode(';', $base64Image);
                    list(, $base64Image)      = explode(',', $base64Image);
                }

                $imageData = base64_decode($base64Image);

                $ext = '.png';
                if (isset($type) && str_contains($type, 'jpeg')) {
                    $ext = '.jpg';
                }

                $fileName  = time() . '_' . Str::random(10) . $ext;
                $targetDir = public_path('media/employee/profile_picture/');

                if (! is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $imagePath = $targetDir . $fileName;
                file_put_contents($imagePath, $imageData);

                // Delete old image if exists
                if ($employee->profile_picture && file_exists($targetDir . $employee->profile_picture)) {
                    unlink($targetDir . $employee->profile_picture);
                }

                $dataToUpdate['profile_picture'] = $fileName;
            }

            unset($dataToUpdate['profile_image_base64']);

            // 4. Update the record
            $employee->update($dataToUpdate);

            // 5. Return success response
            return response()->json([
                'message'  => 'Employee updated successfully!',
                'employee' => $employee,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEmergencyContactNew(Request $request, $id)
    {
        try {
            // 1. Find employee
            $employee = Employee::findOrFail($id);

            // 2. Validate incoming data
            $validatedData = $request->validate([
                'phone_relative_number' => 'nullable|string|max:10',
                'relation'              => 'nullable|string|max:50',
                'local_address'         => 'nullable|string|max:255',
                'local_city'            => 'nullable|string|max:100',
                'local_country'         => 'nullable|string|max:100',
            ]);

            // 4. Update the record
            $employee->update($validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Employee updated successfully!',
                'employee' => $employee,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAddress(Request $request, $id)
    {
        try {
            // 1. Find employee
            $employee = Employee::findOrFail($id);

            // 2. Validate incoming data
            $validatedData = $request->validate([
                'home_address' => 'nullable|string|max:10',
                'home_tel'     => 'nullable|string|max:50',
                'home_mobile'  => 'nullable|string|max:255',
                'home_fax'     => 'nullable|string|max:100',
                'home_city'    => 'nullable|string|max:100',
                'home_state'   => 'nullable|string|max:100',
                'home_country' => 'nullable|string|max:100',
            ]);

            // 4. Update the record
            $employee->update($validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Employee updated successfully!',
                'employee' => $employee,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateVisa(Request $request)
    {
        try {

            // 2. Validate incoming data
            $validatedData = $request->validate([
                "visa_no"            => "required|min:2|max:20",
                "place_of_issues"    => "required|min:1|max:20",
                "country"            => "required|min:1|max:20",
                "issue_date"         => "required|date",
                "expiry_date"        => "required|date",

                "security_amount"    => "nullable",
                "labour_no"          => "required",
                "personal_no"        => "nullable",
                "labour_issue_date"  => "required|date",
                "labour_expiry_date" => "required|date",
                "note"               => "nullable",

                "employee_id"        => "required",
                "company_id"         => "required",
            ]);

            info($validatedData);

            $record = Visa::updateOrCreate(['employee_id' => $request->employee_id, 'company_id' => $request->company_id], $validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Visa Info saved successfully!',
                'employee' => $record,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEmirate(Request $request)
    {
        try {
            // 2. Validate incoming data
            $validatedData = $request->validate([
                "emirate_id"    => "required|min:8|max:20",
                "name"          => "nullable|min:3|max:20",
                "gender"        => "nullable|min:1|max:20",
                "date_of_birth" => "required",
                "nationality"   => "required",
                "issue"         => "required",
                "expiry"        => "required",

                "employee_id"   => "required",
                "company_id"    => "required",
            ]);

            $record = EmiratesInfo::updateOrCreate(['employee_id' => $request->employee_id, 'company_id' => $request->company_id], $validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Emirate Info saved successfully!',
                'employee' => $record,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassport(Request $request)
    {
        try {
            // 2. Validate incoming data
            $validatedData = $request->validate([
                "passport_no"     => "required|min:8|max:20",
                "place_of_issues" => "nullable|min:3|max:20",
                "issue_date"      => "required",
                "expiry_date"     => "required",

                "employee_id"     => "required",
                "company_id"      => "required",
            ]);

            $record = Passport::updateOrCreate(['employee_id' => $request->employee_id, 'company_id' => $request->company_id], $validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Passport Info saved successfully!',
                'employee' => $record,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateQualification(Request $request)
    {
        try {
            // 2. Validate incoming data
            $validatedData = $request->validate([

                "certificate" => "nullable|min:3|max:20",
                "type"        => "nullable|min:1|max:20",
                "collage"     => "nullable|min:3|max:20",
                "start"       => "nullable",
                "end"         => "nullable",

                "employee_id" => "required",
                "company_id"  => "required",
            ]);

            $record = Qualification::updateOrCreate(['employee_id' => $request->employee_id, 'company_id' => $request->company_id], $validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Qualification Info saved successfully!',
                'employee' => $record,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateBank(Request $request)
    {
        try {
            // 2. Validate incoming data
            $validatedData = $request->validate([

                "account_title" => "nullable|min:3|max:20",
                "bank_name"     => "nullable|min:3|max:20",
                "account_no"    => "nullable|min:6|max:20",
                "iban"          => "nullable|min:16|max:24",
                "address"       => "nullable|min:1|max:24",

                "employee_id"   => "required",
                "company_id"    => "required",
            ]);

            $record = BankInfo::updateOrCreate(['employee_id' => $request->employee_id, 'company_id' => $request->company_id], $validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Bank Info saved successfully!',
                'employee' => $record,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAccessSettings(Request $request)
    {
        try {
            // 2. Validate incoming data
            $validatedData = $request->validate([
                'rfid_card_number'   => 'nullable|string|max:10',
                'rfid_card_password' => 'nullable|string|max:50',
            ]);

            // 4. Update the record
            $employee = Employee::where(['employee_id' => $request->employee_id, 'company_id' => $request->company_id])->update($validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Employee updated successfully!',
                'employee' => $employee,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateLogin(Request $request)
    {

        $arr                     = [];
        $arr["name"]             = "null";
        $arr["email"]            = $request->email;
        $arr["company_id"]       = $request->company_id;
        $arr["employee_role_id"] = 0;

        if ($request->password != '' || $request->password != "********") {
            $arr['password'] = Hash::make($request->password ?? "secret");
        }

        try {

            // Try to find existing user
            $user = User::where('email', $request->email)
                ->where('company_id', $request->company_id)
                ->first();

            if ($user) {
                // If found → update existing
                $user->update($arr);
            } else {
                // If not found → create new
                $user = User::create(array_merge([
                    'email'      => $request->email,
                    'company_id' => $request->company_id,
                ], $arr));
            }

            $userId = $user->id;

            Employee::where('id', $request->employee_id)
                ->update(['user_id' => $userId]);

            info("User ID assigned: " . $userId);

            // 5. Return success response
            return response()->json([
                'message'  => 'User Info saved successfully!',
                'employee' => $user,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            // 2. Validate incoming data
            $validatedData = $request->validate([
                'leave_group_id'       => 'nullable|string|max:10',
                'reporting_manager_id' => 'nullable|string|max:10',
                'status'               => 'nullable|string|max:50',
            ]);

            // 4. Update the record
            $employee = Employee::where(['employee_id' => $request->employee_id, 'company_id' => $request->company_id])->update($validatedData);

            // 5. Return success response
            return response()->json([
                'message'  => 'Employee updated successfully!',
                'employee' => $employee,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateDocument(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:10',
        ]);

        $payload = [
            "title" => $request->title,
            "attachment" => (new DocumentInfoController)->saveFile($request->attachment, $request->employee_id),
            "employee_id" => $request->employee_id,
            "company_id" => $request->company_id,
        ];

        try {
            $result = DocumentInfo::create($payload);
            // 5. Return success response
            return response()->json([
                'message'  => 'Document saved updated successfully!',
                'employee' => $result,
            ], 200);
        } catch (ValidationException $e) {
            $indexedErrors = collect($e->errors())->flatten()->all();

            return response()->json([
                'message' => $indexedErrors[0],
                'errors'  => $indexedErrors,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
