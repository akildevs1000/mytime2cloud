<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

class Holidays extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['last_sync_at'];

    protected $casts = [
        'created_at' => 'datetime:d-M-y',
        'updated_at' => 'datetime:d-M-y H:i',
    ];

    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, "branch_id");
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    protected function lastSyncAt(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->updated_at?->format('d-M-y H:i') ?? 'Never',
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->clearHolidayCache();
        });

        static::deleted(function ($model) {
            $model->clearHolidayCache();
        });
    }

    public function clearHolidayCache()
    {
        $period = CarbonPeriod::create($this->start_date, $this->end_date);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            Cache::forget("holiday_{$this->company_id}_{$dateString}");
        }
    }

    public static function isHoliday(int $companyId, string $date): bool
    {
        return Cache::remember("holiday_{$companyId}_{$date}", 3600, function () use ($companyId, $date) {
            return static::where('company_id', $companyId)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();
        });
    }
}
