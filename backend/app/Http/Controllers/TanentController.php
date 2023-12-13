<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tanent\StoreRequest;
use App\Http\Requests\Tanent\UpdateRequest;

use App\Models\Tanent;

class TanentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Tanent::where("company_id", request("company_id"))->with(["members", "floor", "room"])->paginate(request("per_page") ?? 10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateTanent(StoreRequest $request)
    {
        try {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $request->phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }

            return $this->response('Tanent Successfully created.', $request->validated(), true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }




    public function store(StoreRequest $request)
    {

        try {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $request->phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }

            $data = $request->validated();

            $data["full_name"] = "{$data["first_name"]} {$data["last_name"]}";

            $communityId = $request->floor_id ?? 1001;
            $shortYear = date("y");
            $floor_id = $request->floor_id;
            $tanentId = Tanent::max('id') + 1;
    
            $data["system_user_id"] = "{$communityId}{$shortYear}{$floor_id}{$tanentId}";

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            $record = Tanent::create($data);

            if ($record) {
                return $this->response('Tanent Successfully created.', $record, true);
            } else {
                return $this->response('Tanent cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function processTanentFile($name)
    {
        if (request($name)) {
            $file = request()->file($name);
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $file->move(public_path("/community/$name"), $fileName);
            $data[$name] = $fileName;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tanent  $Tanent
     * @return \Illuminate\Http\Response
     */

    public function validateUpdateTanent(UpdateRequest $request, $id)
    {
        $Tanent = Tanent::where("id", $id)->first();

        $phone_number = $request->phone_number;

        if ($Tanent->phone_number != $phone_number) {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }
        }

        return $this->response('Tanent successfully updated.', null, true);
    }


    public function tanentUpdate(UpdateRequest $request, $id)
    {
        $Tanent = Tanent::where("id", $id)->first();

        $phone_number = $request->phone_number;

        if ($Tanent->phone_number != $phone_number) {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }
        }

        try {

            $data = $request->validated();

            $data["full_name"] = "{$data["first_name"]} {$data["last_name"]}";

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }
    
            if (isset($request->passport_doc)) {
                $file = $request->file('passport_doc');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('passport_doc')->move(public_path('/community/passport_doc'), $fileName);
                $data['passport_doc'] = $fileName;
            }
    
            if (isset($request->id_doc)) {
                $file = $request->file('id_doc');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('id_doc')->move(public_path('/community/id_doc'), $fileName);
                $data['id_doc'] = $fileName;
            }
    
            if (isset($request->contract_doc)) {
                $file = $request->file('contract_doc');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('contract_doc')->move(public_path('/community/contract_doc'), $fileName);
                $data['contract_doc'] = $fileName;
            }
    
            if (isset($request->ejari_doc)) {
                $file = $request->file('ejari_doc');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('ejari_doc')->move(public_path('/community/ejari_doc'), $fileName);
                $data['ejari_doc'] = $fileName;
            }
    
            if (isset($request->license_doc)) {
                $file = $request->file('license_doc');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('license_doc')->move(public_path('/community/license_doc'), $fileName);
                $data['license_doc'] = $fileName;
            }
    
            if (isset($request->others_doc)) {
                $file = $request->file('others_doc');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('others_doc')->move(public_path('/community/others_doc'), $fileName);
                $data['others_doc'] = $fileName;
            }
            
            $record = $Tanent->update($data);

            return $this->response('Tanent successfully updated.', $record, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tanent  $Tanent
     * @return \Illuminate\Http\Response
     */

    public function destroy(Tanent $Tanent)
    {
        try {
            if ($Tanent->delete()) {
                return $this->response('Tanent successfully deleted.', null, true);
            } else {
                return $this->response('Tanent cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
