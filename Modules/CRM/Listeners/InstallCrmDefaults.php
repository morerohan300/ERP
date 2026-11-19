<?php

declare(strict_types=1);

namespace Modules\CRM\Listeners;

use Modules\Core\Events\CompanyCreated;
use Modules\CRM\Services\CrmSetupService;

/** Gives every new company the CRM starting data (a Core event, handled here without Core knowing about CRM). */
class InstallCrmDefaults
{
    public function __construct(private readonly CrmSetupService $setup) {}

    public function handle(CompanyCreated $event): void
    {
        $this->setup->install($event->company);
    }
}
