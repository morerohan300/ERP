<?php

declare(strict_types=1);

namespace Modules\Core\Database\Factories;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Tax;

/**
 * @extends Factory<Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rate = fake()->randomElement([5, 10, 12, 18, 20]);

        return [
            'company_id' => fn () => app(CurrentCompany::class)->id() ?? Company::factory(),
            'name' => "VAT {$rate}%",
            'code' => 'VAT'.$rate.fake()->unique()->numerify('##'),
            'rate' => $rate,
            'type' => 'percent',
            'scope' => 'both',
            'is_compound' => false,
            'is_inclusive' => false,
            'is_active' => true,
        ];
    }

    public function inclusive(): static
    {
        return $this->state(fn () => ['is_inclusive' => true]);
    }
}
