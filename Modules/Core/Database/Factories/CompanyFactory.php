<?php

declare(strict_types=1);

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'legal_name' => $name.' Ltd.',
            'tax_number' => strtoupper(fake()->bothify('##??#####?#Z#')),
            'registration_number' => fake()->numerify('REG-######'),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => 'https://'.fake()->domainName(),
            'address' => [
                'line1' => fake()->streetAddress(),
                'line2' => null,
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'US',
            ],
            'base_currency_code' => 'USD',
            'locale' => 'en',
            'timezone' => 'UTC',
            'fiscal_year_start_month' => 1,
            'date_format' => 'd M Y',
            'industry' => fake()->randomElement(['retail', 'wholesale', 'manufacturing', 'services', 'distribution', 'agency']),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
