<?php

namespace App\Models;

use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\CurrentCompany;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Core\Models\Company;
use Modules\Core\Models\LoginHistory;
use Spatie\Permission\Traits\HasRoles;

/**
 * Application user. Users are global; their access to a company, its
 * branches and their roles (spatie teams = company) are per company.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property bool $is_active
 * @property int|null $current_company_id
 * @property string|null $locale
 * @property string|null $timezone
 */
#[Fillable(['name', 'email', 'password', 'phone', 'avatar_path', 'locale', 'timezone', 'current_company_id', 'is_active'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuid, LogsModelActivity, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /** @var array<int, bool> company id => is owner */
    private array $ownershipCache = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot(['branch_ids', 'department_id', 'job_title', 'is_owner', 'joined_at'])
            ->withTimestamps();
    }

    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class)->latest('occurred_at');
    }

    /** @param Builder<User> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Users that belong to the active company.
     *
     * @param  Builder<User>  $query
     */
    public function scopeInCurrentCompany(Builder $query): void
    {
        $companyId = app(CurrentCompany::class)->id();

        $query->whereHas('companies', fn (Builder $q) => $q->whereKey($companyId ?? 0));
    }

    public function belongsToCompany(Company|int $company): bool
    {
        $id = $company instanceof Company ? $company->getKey() : $company;

        return $this->companies()->whereKey($id)->exists();
    }

    /** Owners have every permission within their company. */
    public function isOwnerOfCurrentCompany(): bool
    {
        $companyId = app(CurrentCompany::class)->id();
        if ($companyId === null) {
            return false;
        }

        return $this->ownershipCache[$companyId] ??= $this->companies()
            ->whereKey($companyId)
            ->wherePivot('is_owner', true)
            ->exists();
    }

    /** Clear memoised ownership flags (after membership changes). */
    public function flushOwnershipCache(): void
    {
        $this->ownershipCache = [];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function initials(): string
    {
        return str($this->name)->explode(' ')->take(2)->map(fn (string $w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }
}
