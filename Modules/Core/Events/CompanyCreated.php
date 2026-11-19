<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Company;

/**
 * Fired after a company and its defaults are created. Other modules listen
 * to seed their own defaults (chart of accounts, pipelines, leave types ...).
 */
final class CompanyCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Company $company,
        public readonly User $owner,
    ) {}
}
