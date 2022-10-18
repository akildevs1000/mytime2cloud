<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $with = ['shift_type'];

    protected $casts = [
        'days' => 'array',
        // 'beginning_date' => 'datetime:d-M-y',
    ];

    public function time_table()
    {
        return $this->hasOne(TimeTable::class);
    }

    public function shift_type()
    {
        return $this->belongsTo(ShiftType::class);
    }

    protected static function boot()
    {
        parent::boot();
        // Order by name DESC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('id', 'desc');
        });
    }
}