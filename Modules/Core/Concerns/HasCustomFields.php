<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Core\Models\CustomFieldValue;

/**
 * Gives a model company-defined custom fields. The model's morph alias is
 * used as the custom field `entity_type`.
 *
 * @mixin Model
 */
trait HasCustomFields
{
    public static function bootHasCustomFields(): void
    {
        static::deleting(function ($model): void {
            if (! method_exists($model, 'isForceDeleting') || $model->isForceDeleting()) {
                $model->customFieldValues()->delete();
            }
        });
    }

    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }

    /**
     * Values keyed by field name.
     *
     * @return array<string, mixed>
     */
    public function customFieldData(): array
    {
        return $this->customFieldValues
            ->loadMissing('field')
            ->filter(fn (CustomFieldValue $v) => $v->field !== null)
            ->mapWithKeys(fn (CustomFieldValue $v) => [
                $v->field->name => $v->field->type === 'multiselect' ? json_decode((string) $v->value, true) : $v->value,
            ])
            ->all();
    }

    public static function customFieldEntityType(): string
    {
        return (new static)->getMorphClass();
    }
}
