<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Events\LeadConverted;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\CrmAccount;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\LeadStatus;

/**
 * Lead lifecycle: creation with sensible defaults, and conversion into a
 * contact, an account and a deal.
 */
class LeadService
{
    public function __construct(
        private readonly DealService $deals,
        private readonly CustomFieldService $customFields,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $customFieldValues
     */
    public function create(array $data, User $by, array $customFieldValues = []): Lead
    {
        $data['lead_status_id'] ??= LeadStatus::initial()?->getKey();
        $data['owner_id'] ??= $by->getKey();

        return DB::transaction(function () use ($data, $customFieldValues): Lead {
            $lead = Lead::query()->create($data);
            $this->customFields->save($lead, $customFieldValues);

            return $lead->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $customFieldValues
     */
    public function update(Lead $lead, array $data, array $customFieldValues = []): Lead
    {
        return DB::transaction(function () use ($lead, $data, $customFieldValues): Lead {
            $lead->fill($data)->save();
            $this->customFields->save($lead, $customFieldValues);

            return $lead->refresh();
        });
    }

    /**
     * Turn a lead into a contact, plus an account (new from the company name, or an existing one)
     * and a deal in the default pipeline. Fires LeadConverted.
     *
     * @throws ValidationException when the lead was already converted
     */
    public function convert(
        Lead $lead,
        bool $createDeal = true,
        ?string $dealTitle = null,
        ?string $dealValue = null,
        ?int $existingAccountId = null,
    ): ConversionResult {
        return DB::transaction(function () use ($lead, $createDeal, $dealTitle, $dealValue, $existingAccountId): ConversionResult {
            /** @var Lead $lead */
            $lead = Lead::query()->lockForUpdate()->findOrFail($lead->getKey());

            if ($lead->isConverted()) {
                throw ValidationException::withMessages(['lead' => __('This lead has already been converted.')]);
            }

            $account = $this->resolveAccount($lead, $existingAccountId);

            $contact = new Contact([
                'crm_account_id' => $account?->getKey(),
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'job_title' => $lead->title,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'address' => $lead->address,
                'owner_id' => $lead->owner_id,
            ]);
            $contact->source_lead_id = $lead->getKey();
            $contact->save();

            $deal = $createDeal ? $this->deals->create([
                'title' => $dealTitle ?: ($lead->company_name ?: $lead->full_name).' – '.__('Opportunity'),
                'contact_id' => $contact->getKey(),
                'crm_account_id' => $account?->getKey(),
                'owner_id' => $lead->owner_id,
                'value' => $dealValue ?? $lead->estimated_value ?? '0',
                'currency_code' => $lead->currency_code,
                'branch_id' => $lead->branch_id,
            ]) : null;

            $lead->forceFill([
                'converted_at' => now(),
                'converted_contact_id' => $contact->getKey(),
                'converted_account_id' => $account?->getKey(),
                'converted_deal_id' => $deal?->getKey(),
                'lead_status_id' => LeadStatus::forConversion()?->getKey() ?? $lead->lead_status_id,
            ])->save();

            LeadConverted::dispatch($lead, $contact, $account, $deal);

            return new ConversionResult($lead, $contact, $account, $deal);
        });
    }

    private function resolveAccount(Lead $lead, ?int $existingAccountId): ?CrmAccount
    {
        if ($existingAccountId !== null) {
            return CrmAccount::query()->findOrFail($existingAccountId);
        }

        if (blank($lead->company_name)) {
            return null;
        }

        $account = new CrmAccount([
            'name' => $lead->company_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'billing_address' => $lead->address,
            'owner_id' => $lead->owner_id,
        ]);
        $account->source_lead_id = $lead->getKey();
        $account->save();

        return $account;
    }
}
