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
    public function store(StoreRequest $request)
    {
        try {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $request->phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }

            $data = $request->validated();

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            if (isset($request->attachment)) {
                $file = $request->file('attachment');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('attachment')->move(public_path('/community/attachment'), $fileName);
                $data['attachment'] = $fileName;
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tanent  $Tanent
     * @return \Illuminate\Http\Response
     */
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

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('attachment')->move(public_path('/community/attachment'), $fileName);
                $data['attachment'] = $fileName;
            }

            // If the Tanent number is the same or it's unique, update the Tanent
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
