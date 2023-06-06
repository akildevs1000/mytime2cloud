<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeTimezoneMapping\StoreRequest;
use App\Http\Requests\EmployeeTimezoneMapping\UpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeTimezoneMapping;
use function PHPUnit\Framework\isJson;
use Illuminate\Http\Request;

class EmployeeTimezoneMappingController extends Controller
{
    public function index(EmployeeTimezoneMapping $model, Request $request)
    {
        return $model->where('company_id', $request->company_id)->paginate($request->per_page);
    }
    public function show(EmployeeTimezoneMapping $model, $id)
    {
        return $model->where("id", $id)->first();
    }

    public function store(StoreRequest $request)
    {

        try {
            $record = EmployeeTimezoneMapping::create($request->validated());

            if ($record) {

                $SDKjsonRequest = $this->processSDKrequestjson($record);

                $SDKObj = new SDKController;
                $SDKresponse = ($SDKObj->processSDKRequest("localhost:5000/Person/AddRange", $SDKjsonRequest));

                $finalArray['SDKRequest'] = $SDKjsonRequest;
                $finalArray['SDKResponse'] = json_decode($SDKresponse, true);

                $finalArray['recordResponse'] = $record;
                return $this->response('EmployeeTimezoneMapping Successfully created.', $finalArray, true);
            } else {
                return $this->response('EmployeeTimezoneMapping cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function filterRequestpayloadBySDKResponse($request, $SDKresponse)
    {
        $SDKresponse = json_decode(json_encode($SDKresponse), true);

        $newRequestDevicesidArray = [];

        foreach ($request->device_id as $device) {

            foreach ($SDKresponse['data'] as $responseDevice) {

                if ($device['device_id'] == $responseDevice['sn'] && $responseDevice['message'] == '') {
                    $newRequestDevicesidArray[] = $device;
                }
            }
        }

        return $newRequestDevicesidArray;
    }
    public function processSDKrequestjson($phpArray)
    {

        $finalArray = [];
        if (!isJson($phpArray)) {
            $phpArray = json_decode($phpArray, true);
        } else {
            $phpArray = $phpArray;
        }

        $personsListArray = [];
        $snListArray = array_column($phpArray['device_id'], 'device_id');

        foreach ($phpArray['employee_id'] as $list) {

            //update timezone id in employee table
            $data['timezone_id'] = $phpArray['timezone_id'];
            $record = Employee::find($list['id']);
            $record->update($data);

            $row = [];
            $row['name'] = $list['display_name'];
            $row['userCode'] = $list['system_user_id'];
            //$row['expiry'] = "2089-12-31 23:59:59";
            $row['timeGroup'] = $phpArray['timezone_id'];
            //francsis
            // $row['faceImage'] = "https://backend.ideahrms.com/media/employee/profile_picture/WhatsApp%20Image%202023-01-13%20at%201.21.40%20PM.jpeg";
            //Venu
            $row['faceImage'] = "https://backend.ideahrms.com/media/employee/profile_picture/1685350538.jpeg";

            // $row['faceImage']  = $list['profile_picture'];
            $personsListArray[] = $row;
        }

        $finalArray['snList'] = $snListArray;
        $finalArray['personList'] = $personsListArray;
        return $finalArray;
    }
    public function filterArrayByKeys(array $input, array $column_keys)
    {
        $result = array();
        $column_keys = array_flip($column_keys); // getting keys as values
        foreach ($input as $key => $val) {
            // getting only those key value pairs, which matches $column_keys
            $result[$key] = array_intersect_key($val, $column_keys);
        }
        return $result;
    }
    public function update(UpdateRequest $request, EmployeeTimezoneMapping $EmployeeTimezoneMapping)
    {
        try {

            //updating default timezone id which are already exist in TimezoneName
            if ($request->timezone_id) {
                Employee::where('timezone_id', $request->timezone_id)
                    ->update(['timezone_id' => 1]);
            }

            $record = $EmployeeTimezoneMapping->update($request->all());

            if ($record) {

                $SDKjsonRequest = $this->processSDKrequestjson($request->all());

                $SDKObj = new SDKController;
                $SDKresponse = ($SDKObj->processSDKRequest("localhost:5000/Person/AddRange", $SDKjsonRequest));

                $finalArray['SDKRequest'] = $SDKjsonRequest;
                $finalArray['SDKResponse'] = json_decode($SDKresponse, true);

                $finalArray['recordResponse'] = $request->all();
                return $this->response('EmployeeTimezoneMapping successfully updated.', $finalArray, true);
            } else {
                return $this->response('EmployeeTimezoneMapping cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(EmployeeTimezoneMapping $EmployeeTimezoneMapping)
    {
        $record = $EmployeeTimezoneMapping->delete();

        if ($record) {
            return $this->response('EmployeeTimezoneMapping successfully deleted.', $record, true);
        } else {
            return $this->response('EmployeeTimezoneMapping cannot delete.', null, false);
        }
    }
    public function deleteTimezone(Request $request)
    {

        if ($request->timezone_id) {
            Employee::where('timezone_id', $request->timezone_id)
                ->update(['timezone_id' => 1]);
        }
        $record = EmployeeTimezoneMapping::where('id', $request->id)->delete();

        // //updating default timezone id which are already exist in TimezoneName

        if ($record) {
            return $this->response('EmployeeTimezoneMapping successfully deleted.', $record, true);
        } else {
            return $this->response('EmployeeTimezoneMapping cannot delete.', null, false);
        }
    }
    public function get_employeeswith_timezonename(Employee $employee, Request $request)
    {
        $employees['data'] = $employee
            ->with(["timezone"])
            ->where('company_id', $request->company_id)
            ->when($request->filled('department_id'), function ($q) use ($request) {
                if ($request->department_id != '---') {
                    $q->where('department_id', $request->department_id);
                }

            })
            ->get();
        return $employees;
    }
    public function gettimezonesinfo(EmployeeTimezoneMapping $model, Request $request)
    {

        return $model->with(["timezone"])->where('company_id', $request->company_id)->paginate($request->per_page);
    }
}