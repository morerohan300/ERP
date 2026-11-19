<?php

declare(strict_types=1);

namespace Modules\Core\Database\Factories;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\CustomField;

/**
 * @extends Factory<CustomField>
 */
class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = ucfirst(fake()->unique()->words(2, true));

        return [
            'company_id' => fn () => app(CurrentCompany::class)->id() ?? Company::factory(),
            'entity_type' => 'user',
            'name' => str($label)->snake()->toString(),
            'label' => $label,
            'type' => 'text',
            'is_required' => false,
            'show_in_table' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function select(array $options): static
    {
        return $this->state(fn () => ['type' => 'select', 'options' => $options]);
    }

    public function required(): static
    {
        return $this->state(fn () => ['is_required' => true]);
    }
}
