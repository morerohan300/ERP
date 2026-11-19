<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Exports\QueryExport;
use Modules\CRM\Http\Requests\ConvertLeadRequest;
use Modules\CRM\Http\Requests\LeadRequest;
use Modules\CRM\Models\EmailTemplate;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\LeadSource;
use Modules\CRM\Models\LeadStatus;
use Modules\CRM\Services\LeadService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class LeadController extends Controller
{
    public function __construct(private readonly LeadService $leads)
    {
        $this->authorizeResource(Lead::class, 'lead');
    }

    public function index(): View
    {
        return view('crm::leads.index');
    }

    public function create(): View
    {
        return view('crm::leads.create', ['lead' => null, ...$this->options()]);
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $lead = $this->leads->create($request->payload(), $request->user(), $request->customFields());

        return $this->status('crm.leads.show', __('Lead :number created.', ['number' => $lead->number]), $lead);
    }

    public function show(Lead $lead): View
    {
        $lead->load(['source', 'status', 'owner', 'convertedContact', 'convertedAccount', 'convertedDeal', 'activities.owner', 'emailLogs']);

        return view('crm::leads.show', [
            'lead' => $lead,
            'templates' => EmailTemplate::query()->active()->orderBy('name')->get(['id', 'name', 'subject', 'body']),
            'accounts' => $this->accountOptions(),
        ]);
    }

    public function edit(Lead $lead): View
    {
        return view('crm::leads.edit', ['lead' => $lead, ...$this->options()]);
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->leads->update($lead, $request->payload(), $request->customFields());

        return $this->status('crm.leads.show', __('Lead updated.'), $lead);
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return $this->status('crm.leads.index', __('Lead deleted.'));
    }

    public function convert(ConvertLeadRequest $request, Lead $lead): RedirectResponse
    {
        $result = $this->leads->convert(
            $lead,
            $request->boolean('create_deal', true),
            $request->validated('deal_title'),
            $request->validated('deal_value') !== null ? (string) $request->validated('deal_value') : null,
            $request->validated('account_id') !== null ? (int) $request->validated('account_id') : null,
        );

        $message = __('Lead converted to a contact.');

        return $result->deal !== null && $request->user()->can('crm.deal.view')
            ? $this->status('crm.deals.show', $message, $result->deal)
            : $this->status('crm.contacts.show', $message, $result->contact);
    }

    public function export(): BinaryFileResponse
    {
        Gate::authorize('export', Lead::class);

        return Excel::download(new QueryExport(
            Lead::query()->with(['source', 'status', 'owner'])->orderBy('id'),
            [__('Number'), __('Name'), __('Company'), __('Email'), __('Phone'), __('Source'), __('Status'), __('Score'), __('Owner'), __('Estimated value'), __('Created')],
            fn (Lead $lead): array => [
                $lead->number, $lead->full_name, $lead->company_name, $lead->email, $lead->phone, $lead->source?->name,
                $lead->status?->name, $lead->score, $lead->owner?->name, $lead->estimated_value, $lead->created_at?->toDateString(),
            ],
        ), 'leads-'.now()->toDateString().'.xlsx');
    }

    /** @return array<string, mixed> */
    private function options(): array
    {
        return [
            'sources' => LeadSource::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'statuses' => LeadStatus::query()->where('is_converted', false)->orderBy('sort_order')->pluck('name', 'id')->all(),
            'owners' => $this->userOptions(),
            'branches' => $this->branchOptions(),
            'currencies' => $this->currencyOptions(),
        ];
    }
}
