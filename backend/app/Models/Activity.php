<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['date_time'];

    /**
     * Get the user that owns the Activity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->user()->with("employee");
    }
    public function company()
    {
        return $this->hasOne(Company::class, "id", "company_id");
    }
    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function getDateTimeAttribute()
    {
        return date("d M Y  H:i:s", strtotime($this->created_at));
    }
}
