<?php

namespace App\Models;

use App\Models\Leave;
use App\Models\Timezone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasFactory;

    // protected $with = [];

    protected $with = ["schedule", "department", "designation", "department", "sub_department"];

    protected $guarded = [];

    protected $casts = [
        'joining_date' => 'date:Y/m/d',
        'created_at' => 'datetime:d-M-y',
    ];

    protected $appends = ['show_joining_date', 'edit_joining_date', 'full_name', 'name_with_user_id'];

    public function schedule()
    {
        return $this->hasOne(ScheduleEmployee::class, "employee_id", "system_user_id")
            ->where('from_date', '<=', date('Y-m-d'))
            ->where('to_date', '>=', date('Y-m-d'))
            ->orderBy('from_date', 'desc')
            ->withDefault([
                "shift_type_id" => "---",
                "shift_type" => [
                    "name" => "---",
                ],
            ]);
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class)->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            "email" => "---",
        ]);
    }

    public function timezone()
    {
        return $this->belongsTo(Timezone::class, 'timezone_id', 'timezone_id')->withDefault([
            "timezone_name" => "---",
        ]);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class)->withDefault([
            "name" => "---",
        ]);
    }
    public function leave_group()
    {
        return $this->belongsTo(LeaveGroups::class, "leave_group_id", "id");
    }
    public function role()
    {
        return $this->belongsTo(Role::class)->withDefault([
            "name" => "---",
        ]);
    }

    public function payroll()
    {
        return $this->hasOne(Payroll::class);
    }

    public function passport()
    {
        return $this->hasOne(Passport::class)->withDefault([
            "passport_no" => "---",
            "country" => "---",

        ]);
    }

    public function emirate()
    {
        return $this->hasOne(EmiratesInfo::class)->withDefault([
            "emirate_id" => "---",
        ]);
    }

    public function qualification()
    {
        return $this->hasOne(Qualification::class)->withDefault([
            "certificate" => "---",
        ]);
    }

    public function bank()
    {
        return $this->hasOne(BankInfo::class)->withDefault([
            "bank_name" => "---",
            "account_no" => "---",
            "account_title" => "---",
            "address" => "---",
            "iban" => "---",
        ]);
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->withDefault([
            "name" => "---",
        ]);
    }

    public function sub_department()
    {
        return $this->belongsTo(SubDepartment::class)->withDefault([
            "name" => "---",
        ]);
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
            // $builder->orderBy('id', 'desc');
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

    public function scopeFilter($query, $search)
    {
        $search = strtolower($search);
        $query->when($search ?? false, fn($query, $search) =>
            $query->where(
                fn($query) => $query
                    ->where('employee_id', $search)
                    ->orWhere(DB::raw('lower(first_name)'), 'Like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(last_name)'), 'Like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(phone_number)'), 'Like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(local_email)'), 'Like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(system_user_id)'), 'Like', '%' . $search . '%')
                    ->whereNotNull('first_name')
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
