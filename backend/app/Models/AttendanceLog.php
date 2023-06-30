<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['show_log_time', "time", "date", "edit_date", "hour_only"];

    protected $casts = [
        // 'LogTime' => 'datetime:d-M-y h:i:s:a',
    ];

    public function getTimeAttribute()
    {
        return date("H:i", strtotime($this->LogTime));
    }

    public function getHourOnlyAttribute()
    {
        return date("H", strtotime($this->LogTime));
    }

    public function getShowLogTimeAttribute()
    {
        return strtotime($this->LogTime);
    }

    public function getDateAttribute()
    {
        return date("d-M-y", strtotime($this->LogTime));
    }

    public function getEditDateAttribute()
    {
        return date("Y-m-d", strtotime($this->LogTime));
    }

    public function device()
    {
        return $this->belongsTo(Device::class, "DeviceID", "device_id")->withDefault(["name" => "---", "device_id" => "---"]);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "UserID", "system_user_id");
    }

    public function schedule()
    {
        return $this->belongsTo(ScheduleEmployee::class, "UserID", "employee_id")->withOut(["shift_type"]);
    }

    public function reason()
    {
        return $this->morphOne(Reason::class, 'reasonable');
    }
    public function last_reason()
    {
        return $this->hasOne(Reason::class, "id", "reasonable_id");
    }
}
