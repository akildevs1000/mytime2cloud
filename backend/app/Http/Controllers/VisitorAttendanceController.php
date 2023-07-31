<?php

namespace App\Http\Controllers;

use App\Models\VisitorAttendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VisitorAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 100;

        $company_id = $request->company_id;

        $model = VisitorAttendance::query();

        // Filters
        $model->where('company_id', $company_id);

        $model->when($request->filled('visitor_id'), function ($q) use ($request) {
            $q->where('visitor_id', $request->visitor_id);
            $q->where('company_id', $request->company_id);
        });

        // $model->when(!in_array($request->status, ["SA", "A"]), function ($q) use ($request) {
        //     $q->where('status', $request->status);
        //     $q->where('company_id', $request->company_id);
        // });


        $model->when($request->daily_date && $request->report_type == 'Daily', function ($q) use ($request) {
            $q->whereDate('date', $request->daily_date);
            $q->where('company_id', $request->company_id);
        });

        $model->when($request->from_date && $request->to_date && $request->report_type != 'Daily', function ($q) use ($request) {
            $q->whereBetween("date", [$request->from_date, $request->to_date]);
            $q->where('company_id', $request->company_id);
        });

        $model->when($request->filled('date'), function ($q) use ($request) {
            $q->whereDate('date', '=', $request->date);
            $q->where('company_id', $request->company_id);
        });

        $model->when($request->filled('visitor_first_name') && $request->visitor_first_name != '', function ($q) use ($request) {
            $q->whereHas('visitor', fn (Builder $q) => $q->where('first_name', 'ILIKE', "$request->visitor_first_name%"));
            $q->where('company_id', $request->company_id);
        });

        $model->when($request->filled('in'), function ($q) use ($request) {
            $q->where('in', 'LIKE', "$request->in%");
            $q->where('company_id', $request->company_id);
        });
        $model->when($request->filled('out'), function ($q) use ($request) {
            $q->where('out', 'LIKE', "$request->out%");
            $q->where('company_id', $request->company_id);
        });
        $model->when($request->filled('total_hrs'), function ($q) use ($request) {
            $q->where('total_hrs', 'LIKE', "$request->total_hrs%");
            $q->where('company_id', $request->company_id);
        });

        // Eager loading relationships
        $model->with(['visitor' => function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        }, 'device_in' => function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        }, 'device_out' => function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        }]);

        // Sorting
        $sortBy = $request->input('sortBy', 'date');
        
        $sortDesc = $request->input('sortDesc') === 'true';

        $model->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        return $model->paginate($perPage);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VisitorAttendance  $visitorAttendance
     * @return \Illuminate\Http\Response
     */
    public function show(VisitorAttendance $visitorAttendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VisitorAttendance  $visitorAttendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VisitorAttendance $visitorAttendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VisitorAttendance  $visitorAttendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(VisitorAttendance $visitorAttendance)
    {
        //
    }
}
