<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Modules\Core\Database\Factories\CustomFieldFactory;

/**
 * Company-defined extra field on an entity (contacts, products, deals ...).
 *
 * @property string $entity_type morph alias of the target model
 * @property string $name machine name
 * @property string $label
 * @property string $type
 * @property array<int, string>|null $options
 * @property bool $is_required
 */
#[Fillable([
    'entity_type', 'name', 'label', 'type', 'options', 'default_value', 'help_text',
    'is_required', 'show_in_table', 'is_active', 'sort_order',
])]
class CustomField extends Model
{
    /** @use HasFactory<CustomFieldFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    public const TYPES = ['text', 'textarea', 'number', 'date', 'select', 'multiselect', 'checkbox', 'email', 'url'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'show_in_table' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): CustomFieldFactory
    {
        return CustomFieldFactory::new();
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /** @param Builder<CustomField> $query */
    public function scopeForEntity(Builder $query, string $entityType): void
    {
        $query->where('entity_type', $entityType)->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Laravel validation rules for this field's value.
     *
     * @return array<int, mixed>
     */
    public function validationRules(): array
    {
        $rules = [$this->is_required ? 'required' : 'nullable'];

        return array_merge($rules, match ($this->type) {
            'number' => ['numeric'],
            'date' => ['date'],
            'email' => ['email', 'max:255'],
            'url' => ['url', 'max:255'],
            'checkbox' => ['boolean'],
            'select' => [Rule::in($this->options ?? [])],
            'multiselect' => ['array'],
            'textarea' => ['string', 'max:5000'],
            default => ['string', 'max:255'],
        });
    }
}
