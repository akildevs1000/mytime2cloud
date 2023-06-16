<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->user_type = $this->getUserType($user);

        if (in_array($user->user_type, ["employee", "company"])) {
            $employeeUser = $user->load('company', 'employee');

            if (!$employeeUser->company) {
                throw ValidationException::withMessages([
                    'email' => ['Comapny does not exist.'],
                ]);
            }

            if ($employeeUser->company_id > 0 && $employeeUser->company->expiry < date('Y-m-d')) {
                throw ValidationException::withMessages([
                    'email' => ['Your subscription has been expired.'],
                ]);
            }
        }

        return response()->json([
            'token' => $user->createToken('myApp')->plainTextToken,
            'user' => $user,
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->user_type = $this->getUserType($user);
        $user->permissions = $user->assigned_permissions ? $user->assigned_permissions->permission_names : [];

        if (in_array($user->user_type, ["employee", "company"])) {
            $employeeUser = $user->load('company', 'employee');
            $user->permissions = $employeeUser->assigned_employee_permissions->permission_names ?? [];
        }

        return response()->json(['user' => $user], 200);
    }

    public function getUserType($user)
    {
        if ($user->company_id > 0) {
            return $user->employee_role_id > 0 ? "employee" : "company";
        } else {
            return $user->role_id > 0 ? "user" : "master";
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
    }
}
