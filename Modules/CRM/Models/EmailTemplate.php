<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\EmailTemplateFactory;

/**
 * A reusable email with merge tags such as {{ first_name }} (see EmailTemplateService).
 *
 * @property int $id
 * @property string $name
 * @property string $subject
 * @property string $body
 */
#[Fillable(['name', 'module', 'subject', 'body', 'is_active'])]
class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function newFactory(): EmailTemplateFactory
    {
        return EmailTemplateFactory::new();
    }

    /** @param Builder<EmailTemplate> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
