<?php

declare(strict_types=1);

namespace Modules\Core\Database\Factories;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Department;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Sales', 'Marketing', 'Finance', 'Human Resources', 'Operations', 'Purchasing',
            'Warehouse', 'Customer Support', 'Engineering', 'Production', 'Quality Assurance', 'Legal',
        ]);

        return [
            'company_id' => fn () => app(CurrentCompany::class)->id() ?? Company::factory(),
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 3)),
            'description' => fake()->sentence(),
        ];
    }
}
