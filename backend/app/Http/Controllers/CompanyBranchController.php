<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyBranch\StoreRequest;
use App\Models\CompanyBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyBranchController extends Controller
{
    public function seedDefaultData()
    {
        $arr = [];
        foreach (range(1, 5) as $i) {
            $arr[] = [
                "branch_code" => "NAM" . $i,
                "branch_name" => "BRANCH NAME " . $i,
                "created_date" => "13 Sep 2023",
                'location' => "khalid street",
                'address' => "Ajman",
                'licence_issue_by_department' => "Ajman Economic Department",
                'licence_number' => "0098765",
                'licence_expiry' => "24/09/2024",
                'telephone' => "0554501483",
                'po_box' => "654789",
                'phone' => "0554501483",
                "user_id" => 0,
                "company_id" => 8,
            ];
        }
        CompanyBranch::truncate();
        CompanyBranch::insert($arr);
        return CompanyBranch::count();
    }

    public function store(CompanyBranch $model, StoreRequest $request)
    {
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => $request->password,
        ]);


        $data = $request->except(["name", "email", "password"]);
        $data["created_date"] = date("Y-m-d");
        $data["user_id"] = $user->id;
        $data["branch_code"] = strtoupper(substr($data["branch_name"], 0, 3)) . CompanyBranch::where("company_id", $request->company_id)->orderBy("id", "desc")->value("id") ?? 0;


        if (isset($request->profile_picture)) {

            $file = $request->file('profile_picture');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->file('profile_picture')->move(public_path('/upload'), $fileName);
            $data['logo'] = $fileName;
        }
        unset($data["profile_picture"]);
        try {
            $record = $model->create($data);

            if ($record) {
                return $this->response('Branch successfully added.', null, true);
            } else {
                return $this->response('Branch cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(CompanyBranch $CompanyBranch, Request $request)
    {
        // return $CompanyBranch->filter($request)->paginate($request->per_page ?? 100);
        return $CompanyBranch->with("user")->withCount(["employees", "devices", "departments"])->orderBy("id", "desc")->paginate($request->per_page ?? 100);
    }

    public function destroy($id)
    {
        try {
            $record = CompanyBranch::find($id);

            if (!$record) {
                return response()->json(['message' => 'No such record found.'], 404);
            }

            DB::transaction(function () use ($record) {
                $user_id = $record->user_id;
                $record->delete();
                User::where('id', $user_id)->delete();
            });

            return response()->json(['message' => 'Branch successfully deleted.', 'status' => true], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}