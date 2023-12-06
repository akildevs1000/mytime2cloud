<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tanent extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the user that owns the Room
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getProfilePictureAttribute($value)
    {
        if (!$value) return null;
        return asset('community/profile_picture/' . $value);
    }

    public function getAttachmentAttribute($value)
    {
        if (!$value) return null;
        return asset('community/attachment/' . $value);
    }
}
