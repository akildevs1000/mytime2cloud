<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostCompany extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = "host_companies";

    public function getLogoAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('media/company/logo/' . $value);
    }

    /**
     * Get the employee that owns the HostCompany
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class)->withOut(["schedule", "department", "designation", "department", "sub_department"]);
    }
}
