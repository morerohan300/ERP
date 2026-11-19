<?php

declare(strict_types=1);

namespace Modules\Core\Database\Factories;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = fake()->unique()->city();

        return [
            'company_id' => fn () => app(CurrentCompany::class)->id() ?? Company::factory(),
            'name' => $city.' Branch',
            'code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $city), 0, 3)).fake()->unique()->numberBetween(10, 999),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => ['line1' => fake()->streetAddress(), 'city' => $city, 'country' => 'US'],
            'is_head_office' => false,
            'is_active' => true,
        ];
    }

    public function headOffice(): static
    {
        return $this->state(fn () => ['name' => 'Head Office', 'code' => 'HO', 'is_head_office' => true]);
    }
}
