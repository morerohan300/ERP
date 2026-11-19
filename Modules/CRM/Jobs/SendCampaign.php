<?php

declare(strict_types=1);

namespace Modules\CRM\Jobs;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Company;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Services\CampaignService;

/** Delivers a campaign to its pending recipients, inside the owning company's context. */
class SendCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(public readonly int $companyId, public readonly int $campaignId) {}

    public function handle(CurrentCompany $currentCompany, CampaignService $campaigns): void
    {
        $currentCompany->runAs(Company::query()->findOrFail($this->companyId), function () use ($campaigns): void {
            $campaign = Campaign::query()->find($this->campaignId);

            if ($campaign !== null && $campaign->status === Campaign::STATUS_SENDING) {
                $campaigns->deliver($campaign);
            }
        });
    }
}
