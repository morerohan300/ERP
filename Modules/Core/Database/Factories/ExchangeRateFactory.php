<?php

declare(strict_types=1);

namespace Modules\Core\Database\Factories;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\ExchangeRate;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => fn () => app(CurrentCompany::class)->id() ?? Company::factory(),
            'from_currency' => 'EUR',
            'to_currency' => 'USD',
            'rate' => fake()->randomFloat(6, 1.02, 1.15),
            'effective_date' => now()->toDateString(),
            'source' => 'manual',
        ];
    }
}
