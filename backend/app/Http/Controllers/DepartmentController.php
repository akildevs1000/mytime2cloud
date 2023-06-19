<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\DepartmentRequest;
use App\Http\Requests\Department\DepartmentUpdateRequest;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $cols = $request->cols;

        $model = Department::query();
        $model->where('company_id', $request->company_id);
        $model->with('children');
        $model->with('designations');
        $model->where('company_id', $request->company_id);
        $model->when($request->filled('serach_department_id'), function ($q) use ($request) {
            $q->where('id', 'LIKE', "$request->serach_department_id%");
        });
        $model->when($request->filled('serach_department_name'), function ($q) use ($request) {
            $q->where('name', 'ILIKE', "$request->serach_department_name%");
        });
        $model->when($request->filled('serach_sub_department_name'), function ($q) use ($request) {
            $q->whereHas('children', fn(Builder $query) => $query->where('name', 'ILIKE', "$request->serach_sub_department_name%"));
        });
        $model->when($request->filled('serach_designation_name'), function ($q) use ($request) {
            $q->whereHas('designations', fn(Builder $query) => $query->where('name', 'ILIKE', "$request->serach_designation_name%"));
        });
        $model->when(isset($cols) && count($cols) > 0, function ($q) use ($cols) {
            $q->select($cols);
        });
        return $model->paginate($request->per_page);
    }

    public function search(Request $request, $key)
    {
        $model = Department::query();
        $model->where('id', 'LIKE', "%$key%");
        $model->where('company_id', $request->company_id);
        $model->orWhere('name', 'LIKE', "%$key%");
        return $model->with('children')->paginate($request->per_page);
    }

    public function store(Department $model, DepartmentRequest $request)
    {
        $data = $request->validated();

        if ($request->company_id) {
            $data["company_id"] = $request->company_id;
        }
        try {
            $record = $model->create($data);

            if ($record) {
                return $this->response('Department successfully added.', $record->with('children'), true);
            } else {
                return $this->response('Department cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(Department $Department)
    {
        return $Department->with('children');
    }

    public function update(DepartmentUpdateRequest $request, Department $Department)
    {
        try {
            $record = $Department->update($request->validated());

            if ($record) {
                return $this->response('Department successfully updated.', $Department->with('children'), true);
            } else {
                return $this->response('Department cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(Department $Department)
    {
        try {
            $record = $Department->delete();

            if ($record) {
                return $this->response('Department successfully deleted.', null, true);
            } else {
                return $this->response('Department cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteSelected(Department $model, Request $request)
    {
        try {
            $record = $model->whereIn('id', $request->ids)->delete();

            if ($record) {
                return $this->response('Department successfully deleted.', null, true);
            } else {
                return $this->response('Department cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}