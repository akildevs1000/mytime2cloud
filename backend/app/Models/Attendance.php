<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ["edit_date", "day"];

    protected $casts = [
        'date' => 'date',
    ];

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
        return $value > '04:00' ? '00:00' : $value;
    }

    public function getTotalHrsAttribute($value)
    {
        $value = explode(".", "$value")[0];
        return $value > '12:00' ? '00:00' : $value;
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
        return $this->belongsTo(Device::class, 'device_id_out');
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
}