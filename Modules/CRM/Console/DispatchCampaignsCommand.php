<?php

declare(strict_types=1);

namespace Modules\CRM\Console;

use App\Support\Modules\ModuleManager;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Console\Command;
use Modules\Core\Models\Company;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Services\CampaignService;
use Throwable;

class DispatchCampaignsCommand extends Command
{
    protected $signature = 'crm:dispatch-campaigns';

    protected $description = 'Start every scheduled campaign whose send time has arrived';

    public function handle(CurrentCompany $currentCompany, CampaignService $campaigns, ModuleManager $modules): int
    {
        $started = 0;

        foreach (Company::query()->cursor() as $company) {
            if (! $modules->enabled('crm', $company->getKey())) {
                continue;
            }

            $currentCompany->runAs($company, function () use ($campaigns, &$started): void {
                Campaign::query()
                    ->where('status', Campaign::STATUS_SCHEDULED)
                    ->where('scheduled_at', '<=', now())
                    ->get()
                    ->each(function (Campaign $campaign) use ($campaigns, &$started): void {
                        try {
                            $campaigns->send($campaign);
                            $started++;
                        } catch (Throwable $e) {
                            $campaign->forceFill(['status' => Campaign::STATUS_FAILED])->save();
                            report($e);
                        }
                    });
            });
        }

        $this->components->info("Started {$started} campaign(s).");

        return self::SUCCESS;
    }
}
