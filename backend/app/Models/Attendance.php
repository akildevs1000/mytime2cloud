<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    const ABSENT = "A"; //1;
    const PRESENT = "P"; //2;
    const MISSING = "M"; //3;

    protected $guarded = [];

    protected $appends = [
        "edit_date",
        "day",
    ];

    protected $casts = [
        'date' => 'date',
        'logs' => 'array',
        'shift_type_id' => 'integer',
    ];

    protected $hidden = ["branch_id", "created_at", "updated_at"];
    // protected $hidden = ["company_id", "branch_id", "created_at", "updated_at"];

    public function shift()
    {
        return $this->belongsTo(Shift::class)->withOut("shift_type");
    }

    public function shift_type()
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function getDateAttribute($value)
    {
        return date("d-M-y", strtotime($value));
    }

    public function getDayAttribute()
    {
        return date("D", strtotime($this->date));
    }
    public function getHrsMins($difference)
    {
        $h = floor($difference / 3600);
        $h = $h < 0 ? "0" : $h;
        $m = floor($difference % 3600) / 60;
        $m = $m < 0 ? "0" : $m;

        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    // public function getTotalHrsAttribute($value)
    // {
    //     return strtotime($value) < strtotime('18:00') ? $value : '00:00';
    // }

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device_in()
    {
        return $this->belongsTo(Device::class, 'device_id_in', 'device_id')->withDefault([
            'name' => '---',
        ]);
    }

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device_out()
    {
        return $this->belongsTo(Device::class, 'device_id_out', 'device_id')->withDefault([
            'name' => '---',
        ]);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id", "system_user_id")->withOut("schedule")->withDefault([
            'first_name' => '---',
            "department" => [
                "name" => "---",
            ],
        ]);
    }

    public function employeeAttendance()
    {
        return $this->belongsTo(Employee::class, "employee_id");
    }

    public function getEditDateAttribute()
    {
        return date("Y-m-d", strtotime($this->date));
    }

    public function AttendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, "UserID", "employee_id");
    }

    public function schedule()
    {
        return $this->belongsTo(ScheduleEmployee::class, "employee_id", "employee_id")->withOut(["shift_type"]);
    }

    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            //$builder->orderBy('id', 'desc');
        });
    }

    public function last_reason()
    {
        return $this->hasOne(Reason::class, 'reasonable_id','id')->latest();
    }
}
