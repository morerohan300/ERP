<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Models\User;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Database\Factories\CompanyFactory;

/**
 * Tenant root. Every business record belongs to exactly one company.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $base_currency_code
 * @property string $timezone
 * @property string $locale
 * @property array<string, string>|null $address
 */
#[Fillable([
    'name', 'legal_name', 'tax_number', 'registration_number', 'email', 'phone', 'website',
    'address', 'logo_path', 'base_currency_code', 'locale', 'timezone',
    'fiscal_year_start_month', 'date_format', 'industry', 'is_active',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'tax_number' => 'encrypted',
            'address' => 'array',
            'is_active' => 'boolean',
            'fiscal_year_start_month' => 'integer',
        ];
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['branch_ids', 'department_id', 'job_title', 'is_owner', 'joined_at'])
            ->withTimestamps();
    }

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('is_owner', true);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_code', 'code');
    }

    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'company_currencies', 'company_id', 'currency_code', 'id', 'code')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
    }

    public function fiscalYears(): HasMany
    {
        return $this->hasMany(FiscalYear::class);
    }

    /** @param Builder<Company> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function initials(): string
    {
        return str($this->name)->explode(' ')->take(2)->map(fn (string $w) => mb_substr($w, 0, 1))->implode('');
    }

    public function formattedAddress(): string
    {
        $a = $this->address ?? [];

        return collect([$a['line1'] ?? null, $a['line2'] ?? null, trim(($a['city'] ?? '').' '.($a['postal_code'] ?? '')), $a['state'] ?? null, $a['country'] ?? null])
            ->filter()
            ->implode(', ');
    }
}
