<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function device()
    {
        return $this->belongsTo(Device::class, "DeviceID", "device_id")->withDefault(["name" => "---", "device_id" => "---"]);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class, "UserID", "system_user_id")->with("zone");
    }
}
