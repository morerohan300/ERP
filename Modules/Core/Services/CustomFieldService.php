<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Core\Concerns\HasCustomFields;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldValue;

/**
 * Validation and persistence of custom field values. Form Requests merge
 * `rules()` into their own rules; services call `save()` after saving the
 * owning model.
 */
class CustomFieldService
{
    /**
     * Entity types that support custom fields: morph alias => label.
     * Modules add theirs via CustomFieldService::registerEntity().
     *
     * @var array<string, string>
     */
    private static array $entities = [];

    public static function registerEntity(string $morphAlias, string $label): void
    {
        self::$entities[$morphAlias] = $label;
    }

    /** @return array<string, string> */
    public static function entities(): array
    {
        return self::$entities;
    }

    /** @return Collection<int, CustomField> */
    public function fieldsFor(string $entityType): Collection
    {
        return CustomField::query()->forEntity($entityType)->get();
    }

    /**
     * Validation rules keyed as custom_fields.<name>.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(string $entityType): array
    {
        $rules = [];
        foreach ($this->fieldsFor($entityType) as $field) {
            $rules["custom_fields.{$field->name}"] = $field->validationRules();
            if ($field->type === 'multiselect') {
                $rules["custom_fields.{$field->name}.*"] = [Rule::in($field->options ?? [])];
            }
        }

        return $rules;
    }

    /**
     * @param  Model&HasCustomFields  $model
     * @param  array<string, mixed>  $values  field name => value
     */
    public function save(Model $model, array $values): void
    {
        $fields = $this->fieldsFor($model->getMorphClass())->keyBy('name');

        foreach ($fields as $name => $field) {
            if (! array_key_exists($name, $values)) {
                continue;
            }

            $value = $values[$name];
            $stored = match (true) {
                $value === null || $value === '' => null,
                $field->type === 'multiselect' => json_encode(array_values((array) $value)),
                $field->type === 'checkbox' => $value ? '1' : '0',
                default => (string) $value,
            };

            CustomFieldValue::query()->updateOrCreate(
                ['custom_field_id' => $field->getKey(), 'model_type' => $model->getMorphClass(), 'model_id' => $model->getKey()],
                ['value' => $stored],
            );
        }

        $model->unsetRelation('customFieldValues');
    }
}
