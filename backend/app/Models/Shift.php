<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['time_in_numbers', "show_from_date", "show_to_date"];

    protected $with = ['shift_type'];

    protected $casts = [
        'days' => 'array',
    ];

    public function getShowFromDateAttribute(): string
    {
        return date('d M Y', strtotime($this->from_date));
    }

    public function getShowToDateAttribute(): string
    {
        return date('d M Y', strtotime($this->to_date));
    }

    public function shift_type()
    {
        return $this->belongsTo(ShiftType::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('id', 'desc');
        });
    }

    public function autoshift()
    {
        return $this->hasOne(AutoShift::class);
    }

    public function getTimeInNumbersAttribute()
    {
        return strtotime($this->on_duty_time);
        return date("Y-m-d H:i", strtotime($this->on_duty_time));
    }
}
