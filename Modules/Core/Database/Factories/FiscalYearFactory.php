<?php

declare(strict_types=1);

namespace Modules\Core\Database\Factories;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\FiscalYear;

/**
 * @extends Factory<FiscalYear>
 */
class FiscalYearFactory extends Factory
{
    protected $model = FiscalYear::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = (int) now()->format('Y');

        return [
            'company_id' => fn () => app(CurrentCompany::class)->id() ?? Company::factory(),
            'name' => "FY {$year}",
            'start_date' => "{$year}-01-01",
            'end_date' => "{$year}-12-31",
            'is_closed' => false,
        ];
    }
}
