<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeRequest\StoreRequest;
use App\Http\Requests\ChangeRequest\UpdateRequest;
use App\Models\ChangeRequest;
use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $model = ChangeRequest::query();
        $model->where("company_id", $request->company_id);
        $model->when($request->filled("employee_device_id"), fn ($q) => $q->where('employee_device_id', $request->employee_device_id));
        $model->when($request->filled("request_type"), fn ($q) => $q->where('request_type', $request->request_type));
        $model->when($request->filled("status"), fn ($q) => $q->where('status', $request->status));
        $model->when($request->filled("branch_id"), fn ($q) => $q->where('branch_id', $request->branch_id));
        $model->orderBy("id", "desc");
        return $model->paginate($request->per_page ?? 100);
    }

    public function store(StoreRequest $request)
    {
        try {
            $data = $request->validated();
            if (isset($request->attachment) && $request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('attachment')->move(public_path('/ChangeRequest/attachments'), $fileName);
                $data['attachment'] = $fileName;
            }

            $record = ChangeRequest::create($data);

            if ($record) {
                return $this->response('ChangeRequest created.', $record, true);
            } else {
                return $this->response('ChangeRequest cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(ChangeRequest $ChangeRequest)
    {
        return $ChangeRequest;
    }

    public function updateChangeRequest($id, UpdateRequest $request)
    {
        try {
            $data = $request->validated();

            if (isset($request->attachment) && $request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('attachment')->move(public_path('/ChangeRequest/attachments'), $fileName);
                $data['attachment'] = $fileName;
            }

            $record = ChangeRequest::where("id", $id)->update($data);

            if ($record) {
                return $this->response('ChangeRequest updated.', $record, true);
            } else {
                return $this->response('ChangeRequest cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(ChangeRequest $ChangeRequest)
    {
        if ($ChangeRequest->delete()) {
            return $this->response('ChangeRequest successfully deleted.', null, true);
        } else {
            return $this->response('ChangeRequest cannot delete.', null, false);
        }
    }
}
