<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ["edit_date", "day"];

    protected $casts = [
        'date' => 'date',
    ];

    public function schedule()
    {
        return $this->belongsTo(ScheduleEmployee::class, "employee_id", "employee_id")->withOut("logs")->withDefault([
            "shift_type_id" => "---",
            "shift_type" => [
                "name" => "---",
            ],
        ]);
    }

    public function getDateAttribute($value)
    {
        return date("d-M-y", strtotime($value));
    }

    public function getDayAttribute()
    {
        return date("D", strtotime($this->date));
    }

    public function getOtAttribute($value)
    {
        $value = explode(".", "$value")[0];
        return $value > '06:00' ? '00:00' : $value;
    }

    public function getTotalHrsAttribute($value)
    {
        $value = explode(".", "$value")[0];
        return $value > '18:00' ? '00:00' : $value;
    }

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device_in()
    {
        return $this->belongsTo(Device::class, 'device_id_in');
    }

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device_out()
    {
        return $this->belongsTo(Device::class, 'device_id_out')->withDefault([
            'name' => '---',
        ]);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id", "system_user_id")->withDefault([
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

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class)->withDefault([
            'name' => '---',
        ]);
    }

    public function getEditDateAttribute()
    {
        return date("Y-m-d", strtotime($this->date));
    }

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift_type()
    {
        return $this->belongsTo(ShiftType::class)->withDefault([
            'name' => '---',
        ]);
    }

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function time_table()
    {
        return $this->belongsTo(TimeTable::class);
    }

    public function AttendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, "UserID", "employee_id");
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('id', 'desc');
        });
    }
}
