<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'from_date' => 'datetime:d-M-Y',
        'to_date' => 'datetime:d-M-Y',
        'requested_at' => 'datetime:d-M-Y H:i:s',
    ];
}