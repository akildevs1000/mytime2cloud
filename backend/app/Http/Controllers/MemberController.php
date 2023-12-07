<?php

namespace App\Http\Controllers;

use App\Http\Requests\Member\StoreRequest;

use App\Models\Member;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function memberList($id)
    {
        return Member::where("tanent_id", $id)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, $id)
    {
        try {
            $member = Member::query();
            $member->where("tanent_id", $id);
            $member->delete();
            $member->insert($request->validated());
            return $this->response('Member successfully updated.', $member, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
