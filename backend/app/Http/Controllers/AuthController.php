<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssignedDepartmentEmployee;
use App\Models\CompanyBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function loginwithOTP(Request $request)
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => ['Database is down'],
            ]);
        }

        $user = User::with('company', 'company.contact', 'employee')->where('email', $request->email)
            ->with("company:id,user_id,name,location,logo,company_code,expiry,contact_number,enable_whatsapp_otp")
            ->select()
            ->first();



        if ($user == null) {
            return Response::json([
                'enable_whatsapp_otp' => 0,
                'user_id' => "",
                'message' => 'Invalid Login details',
                'status' => true
            ], 200);
        }
        $user->user_type = $this->getUserType($user);


        if ($user->enable_whatsapp_otp == 1 && $user->company->enable_whatsapp_otp == 1) {
            $mobile_number = $user->user_type == 'employee' ? $user->employee->whatsapp_number : $user->company->contact->whatsapp;



            if ($mobile_number != '')
                $this->generateOTP($mobile_number, $user);
            else {
                return Response::json([
                    'enable_whatsapp_otp' => $user->enable_whatsapp_otp,
                    'user_id' => '',
                    'message' =>  'Mobile Number is not exist',
                    'status' => false
                ], 200);
            }
            return Response::json([
                'enable_whatsapp_otp' => $user->enable_whatsapp_otp,
                'user_id' => $user->id,
                'message' => 'OTP Is generated',
                'mobile_number' => $mobile_number,
                'status' => true
            ], 200);
        } else {
            return Response::json([
                'enable_whatsapp_otp' => 0,
                'user_id' => $user->id,
                'message' => 'Invalid Login Details',
                'status' => true
            ], 200);
        }
    }
    public function generateOTP($mobile_number, $user)
    {
        try {
            $random_number = mt_rand(100000, 999999);
            $user = User::with('company')->find($user->id);
            $user->otp_whatsapp = $random_number;

            if ($user->save()) {
                $msg          = "";

                $msg .= "\n";
                $msg .= "Dear  $user->email, \n";

                $msg .= "\n";
                $msg .= "--------------- \n";
                $msg .= "Your OTP  \n";
                $msg .= "--------------- \n";
                $msg .= "\n";
                $msg .= "$random_number \n";

                $data = [
                    'to'           =>   $mobile_number,
                    'message'      => $msg,
                    'company'      =>  $user->company ?? false,
                    'instance_id'  => $user->company->whatsapp_instance_id,
                    'access_token' => $user->company->whatsapp_access_token,
                    'type'         => 'Login',
                    'userName'        => $user->email ?? "",
                ];


                //if (app()->isProduction()) 
                {
                    (new WhatsappController())->sentOTP($data);
                }
                return $this->response('updated.' . $data, null, true);
            }
        } catch (\Throwable $th) {
            return $this->response($th, null, true);
        }
    }
    public function verifyOTP(Request $request, $otp)
    {
        try {
            $user = User::find($request->userId);
            if ($user->otp_whatsapp == $otp) {
                // $user->is_verified = 1;
                // $user->save();
                return $this->response('updated.', $user, true);
            }
            // $user->is_verified = 0;
            // $user->save();
            return $this->response('updated.', null, false);
        } catch (\Throwable $th) {
            return $this->response($th, null, false);
        }
    }
    public function login(Request $request)
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => ['Database is down'],
            ]);
        }

        $user = User::where('email', $request->email)
            ->with("company:id,user_id,name,location,logo,company_code,expiry")
            ->select(
                // "id",
                // "email",
                // "password",
                // "is_master",
                // "role_id",
                // "company_id",
                // "employee_role_id",
                // "can_login",
                // "web_login_access",
                // "mobile_app_login_access",
            )
            ->first();

        $this->throwErrorIfFail($request, $user);

        // @params User Id, action,type,companyId.
        $this->recordActivity($user->id, "Login", "Authentication", $user->company_id);

        $user->user_type = $this->getUserType($user);

        unset($user->company);
        unset($user->employee);


        $branchesArray = CompanyBranch::where('user_id', $user->id)->select('id', 'branch_name', "logo")->get();
        if (isset($branchesArray[0])) {
            $assigned_branch_id = $branchesArray[0]['id'];

            $user->user_type = "branch";
            $user->branch_name = $branchesArray[0]['branch_name'];
            $user->branch_logo =   $branchesArray[0]['logo'];
        }
        $user->branch_id = CompanyBranch::where('user_id', $user->id)->pluck('id')->first();



        $arr = [
            'token' => $user->createToken('myApp')->plainTextToken,
            'user' => $user,
        ];

        return $arr;
    }

    public function me(Request $request)
    {
        // return User::where("id", ">", 0)->update(["employee_role_id" => 0]);

        // $data = User::withOut("assigned_permissions")->where("employee_role_id", ">", 0)->get(["id", "employee_role_id", "role_id"]);

        // foreach ($data as $key => $value) {
        //     User::where("id", $value->id)->update(["role_id" => $value->employee_role_id]);
        // }

        // return $data;

        $user = $request->user();
        $user->load("company");
        $user->user_type = $this->getUserType($user);
        // $assigned_branch_id = CompanyBranch::where('user_id', $user->id)->pluck('id', 'branch_name')->first();

        $branchesArray = CompanyBranch::where('user_id', $user->id)->select('id', 'branch_name', "logo")->get();
        if (isset($branchesArray[0])) {
            $assigned_branch_id = $branchesArray[0]['id'];

            $user->user_type = "branch";
            $user->branch_name = $branchesArray[0]['branch_name'];
            $user->branch_logo =  $branchesArray[0]['logo'];
        }
        $user->branch_id = CompanyBranch::where('user_id', $user->id)->pluck('id')->first();

        $user->permissions = $user->assigned_permissions ? $user->assigned_permissions->permission_names : [];
        return ['user' => $user];
    }

    public function getUserType($user)
    {

        if ($user->company_id > 0) {

            if ($user->user_type === "company") {
                return $user->user_type;
            }

            $assginedDepartments = $this->getAssignedDepartments($user);

            if (count($assginedDepartments) == 0) {
                $user->assignedDepartments = [];
                return "employee";
            }

            $branchesArray = CompanyBranch::where('user_id', $user->id)->select('id', 'branch_name')->get();
            if (isset($branchesArray[0])) {
                return "branch";
            }
            return "employee";
            // $user->assignedDepartments = $this->getAssignedDepartments($user);
            // //return "branch";
            // return "manager";
        } else {
            return $user->role_id > 0 ? "user" : "master";
        }
    }

    public function getAssignedDepartments($user)
    {
        return (new AssignedDepartmentEmployee)->assginedDepartment($user->employee->id ?? 0)->pluck("id");
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
    }

    public function throwErrorIfFail($request, $user)
    {
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        } else if ($user->company_id > 0 && $user->company->expiry < now()) {
            throw ValidationException::withMessages([
                'email' => ['Subscription has been expired.'],
            ]);
        } else if (!$user->web_login_access && !$user->is_master) {
            throw ValidationException::withMessages([
                'email' => ['Login access is disabled. Please contact your admin.'],
            ]);
        }
    }
}
