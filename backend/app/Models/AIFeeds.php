<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIFeeds extends Model
{
    use HasFactory;

    protected $table = 'ai_feeds';

    protected $fillable = [
        'company_id',
        'type',
        'description',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
