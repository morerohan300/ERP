<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Authentication events. Not tenant-scoped (a login precedes company
 * selection); the UI filters to members of the current company.
 */
#[Fillable(['user_id', 'email', 'ip_address', 'user_agent', 'successful', 'event', 'occurred_at'])]
class LoginHistory extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function browser(): string
    {
        $ua = (string) $this->user_agent;

        return match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            $ua === '' => '—',
            default => 'Other',
        };
    }

    public function platform(): string
    {
        $ua = (string) $this->user_agent;

        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Linux') => 'Linux',
            default => '—',
        };
    }
}
