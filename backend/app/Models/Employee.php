<?php

namespace App\Models;

use App\Models\Leave;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // protected $with = [];

    protected $with = ["schedule", "department"];

    protected $guarded = [];

    protected $casts = [
        'joining_date' => 'date:Y/m/d',
        'created_at' => 'datetime:d-M-y',
    ];

    protected $appends = ['show_joining_date', 'edit_joining_date', 'full_name', 'name_with_user_id'];

    public function schedule()
    {
        return $this->hasOne(ScheduleEmployee::class, "employee_id", "system_user_id")->withDefault([
            "shift_type_id" => "---",
            "shift_type" => [
                "name" => "---",
            ],
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class)->withDefault([
            "name" => "---",
        ]);
    }

    public function payroll()
    {
        return $this->hasOne(Payroll::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sub_department()
    {
        return $this->belongsTo(SubDepartment::class);
    }

    public function getProfilePictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('media/employee/profile_picture/' . $value);
        // return asset(env('BUCKET_URL') . '/' . $value);

    }

    public function getCreatedAtAttribute($value): string
    {
        return date('d M Y', strtotime($value));
    }

    public function getShowJoiningDateAttribute(): string
    {
        return date('d M Y', strtotime($this->joining_date));
    }

    public function getEditJoiningDateAttribute(): string
    {
        return date('Y-m-d', strtotime($this->joining_date));
    }
    public function getFullNameAttribute(): string
    {
        return $this->first_name . " " . $this->last_name;
    }

    public function getNameWithUserIDAttribute()
    {
        return $this->display_name . " - " . $this->employee_id;
    }

    // use Illuminate\Database\Eloquent\Builder;

    protected static function boot()
    {
        parent::boot();

        // Order by name ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('id', 'desc');
        });
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, "employee_id", "employee_id");
    }

    public function announcement()
    {
        return $this->belongsToMany(Announcement::class)->withTimestamps();
    }

    /**
     * The roles that belong to the Employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reportTo()
    {
        return $this->belongsToMany(Employee::class, 'employee_report', 'employee_id', 'report_id')->withTimestamps();
    }

    public function leave()
    {
        return $this->hasMany(Leave::class, 'employee_id', 'employee_id');
    }

    public function scopeFilter($query,  $filter)
    {
        $query->when($filter ?? false, fn ($query, $search) =>
        $query->where(
            fn ($query) => $query
                ->where('employee_id', $search)
                ->orWhere('display_name', 'Like', '%' . $search . '%')
            // ->orWhere('first_name', 'Like', '%' . $search . '%')
            // ->orWhere('last_name', 'Like', '%' . $search . '%')
            // ->orWhere('phone_number', 'Like', '%' . $search . '%')
            // ->orWhere('whatsapp_number', 'Like', '%' . $search . '%')
            // ->orWhere('phone_relative_number', 'Like', '%' . $search . '%')
            // ->orWhere('whatsapp_relative_number', 'Like', '%' . $search . '%')
            // ->orWhereHas(
            //     'user',
            //     fn ($query) =>
            //     $query->Where('email', 'Like', '%' . $search . '%')
            // )
            // ->orWhereHas(
            //     'designation',
            //     fn ($query) =>
            //     $query->Where('name', 'Like', '%' . $search . '%')
            // )
            // ->orWhereHas(
            //     'department',
            //     fn ($query) =>
            //     $query->Where('name', 'Like', '%' . $search . '%')
            // )
        ));
    }
}