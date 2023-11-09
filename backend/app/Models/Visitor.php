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
        return match ($this->status_id) {
            "1" => 'Pending',
            "2" => 'Approved',
            "3" => 'Rejected',
            default => 'Pending' // Handle any other values if needed
        };
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

    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
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
