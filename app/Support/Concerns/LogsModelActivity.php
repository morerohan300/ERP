<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Default audit-log configuration for business models: log dirty
 * fillable attributes only, skip empty logs, tag entries with company_id.
 *
 * @mixin Model
 */
trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(static::activityLogName());
    }

    public static function activityLogName(): string
    {
        return str(class_basename(static::class))->snake()->toString();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->company_id = $this->company_id ?? app(CurrentCompany::class)->id();
    }
}
