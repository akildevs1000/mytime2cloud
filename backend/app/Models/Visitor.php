<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['full_name', 'name_with_user_id', 'status', 'from_date_display', 'to_date_display'];

    protected $casts = [
        "created_at" => "datetime:d-M-Y",


    ];



    public function getLogoAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('media/visitor/logo/' . $value);
    }

    /**
     * Get the user that owns the Visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    // public function status()
    // {
    //     return $this->belongsTo(Status::class);
    // }

    public function getStatusAttribute()
    {
        return $this->getVisitorStatusIds($this->status_id);
    }

    public function getFromDateDisplayAttribute()
    {
        return date("d-M-y", strtotime($this->visit_from));
    }

    public function getToDateDisplayAttribute()
    {
        return date("d-M-y", strtotime($this->visit_to));
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class)->withDefault([
            "name" => "---",
        ]);
    }

    public function getVisitorStatusIds($id = '')
    {
        // $status = [
        //     1 => "pending",
        //     2 => "approved ",
        //     3 => "rejected ",
        //     4 => "gurd_approved ",
        //     5 => "updated_device",
        //     6 => "checked_in",
        //     7 => "checked_out "
        // ];

        $status = [
            ["id" => "1", "name" => "Pending"],
            ["id" => "2", "name" => "Approved"],
            ["id" => "3", "name" => "Rejected"],
            ["id" => "4", "name" => "Gurd Approved"],
            ["id" => "5", "name" => "Updated Device"],
            ["id" => "6", "name" => "Checked In"],
            ["id" => "7", "name" => "Checked Out"]

        ];

        if ($id) {
            $statusName = '';
            foreach ($status as $s) {
                if ($s["id"] === $id) {
                    $statusName = $s["name"];
                    break;
                }
            }


            return  $statusName;
        } else {
            return $status;
        }
    }
    public function attendances()
    {
        return $this->hasMany(VisitorAttendance::class);
    }
    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }
    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, "branch_id");
    }
    public function timezone()
    {
        return $this->belongsTo(Timezone::class, 'timezone_id', 'timezone_id')->withDefault([
            "timezone_name" => "---",
        ]);
    }

    public function host()
    {
        return $this->belongsTo(HostCompany::class, 'host_company_id')
            ->with("employee:id,user_id,employee_id,system_user_id,first_name,last_name,display_name,profile_picture,phone_number,branch_id");
    }


    public function getNameWithUserIDAttribute()
    {
        return $this->first_name . " - " . $this->system_user_id;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . " " . $this->last_name;
    }
}
