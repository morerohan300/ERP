<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\SegmentFactory;

/**
 * A group of contacts: dynamic (rules evaluated on demand) or static (hand-picked members).
 *
 * @property int $id
 * @property string $name
 * @property bool $is_dynamic
 * @property ?array<int, array{field: string, operator: string, value: ?string}> $rules
 */
#[Fillable(['name', 'description', 'rules', 'is_dynamic'])]
class Segment extends Model
{
    /** @use HasFactory<SegmentFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'is_dynamic' => 'boolean',
        ];
    }

    protected static function newFactory(): SegmentFactory
    {
        return SegmentFactory::new();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'segment_contact');
    }
}
