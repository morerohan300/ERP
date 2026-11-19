<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Exports\QueryExport;
use Modules\CRM\Http\Requests\DealLoseRequest;
use Modules\CRM\Http\Requests\DealRequest;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Services\DealService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DealController extends Controller
{
    public function __construct(private readonly DealService $deals)
    {
        $this->authorizeResource(Deal::class, 'deal');
    }

    public function index(): View
    {
        return view('crm::deals.index');
    }

    public function create(): View
    {
        return view('crm::deals.create', ['deal' => null, 'preset' => request()->only(['contact_id', 'crm_account_id']), ...$this->options()]);
    }

    public function store(DealRequest $request): RedirectResponse
    {
        $deal = $this->deals->create($request->payload(), $request->lines() ?? [], $request->customFields());

        return $this->status('crm.deals.show', __('Deal :number created.', ['number' => $deal->number]), $deal);
    }

    public function show(Deal $deal): View
    {
        $deal->load(['pipeline.stages', 'stage', 'contact', 'account', 'owner', 'products', 'activities.owner']);

        return view('crm::deals.show', ['deal' => $deal]);
    }

    public function edit(Deal $deal): View
    {
        $deal->load('products');

        return view('crm::deals.edit', ['deal' => $deal, 'preset' => [], ...$this->options()]);
    }

    public function update(DealRequest $request, Deal $deal): RedirectResponse
    {
        $this->deals->update($deal, $request->payload(), $request->lines(), $request->customFields());

        return $this->status('crm.deals.show', __('Deal updated.'), $deal);
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $deal->delete();

        return $this->status('crm.deals.index', __('Deal deleted.'));
    }

    public function win(Deal $deal): RedirectResponse
    {
        Gate::authorize('update', $deal);
        $this->deals->win($deal);

        return $this->status('crm.deals.show', __('Deal marked as won.'), $deal);
    }

    public function lose(DealLoseRequest $request, Deal $deal): RedirectResponse
    {
        $this->deals->lose($deal, $request->validated('reason'));

        return $this->status('crm.deals.show', __('Deal marked as lost.'), $deal);
    }

    public function reopen(Deal $deal): RedirectResponse
    {
        Gate::authorize('update', $deal);
        $this->deals->reopen($deal);

        return $this->status('crm.deals.show', __('Deal reopened.'), $deal);
    }

    public function export(): BinaryFileResponse
    {
        Gate::authorize('export', Deal::class);

        return Excel::download(new QueryExport(
            Deal::query()->with(['stage', 'account', 'contact', 'owner'])->orderBy('id'),
            [__('Number'), __('Title'), __('Account'), __('Contact'), __('Stage'), __('Outcome'), __('Value'), __('Probability %'), __('Expected close'), __('Owner'), __('Created')],
            fn (Deal $d): array => [
                $d->number, $d->title, $d->account?->name, $d->contact?->full_name, $d->stage?->name, $d->outcome(),
                $d->value, $d->probability, $d->expected_close_date?->toDateString(), $d->owner?->name, $d->created_at?->toDateString(),
            ],
        ), 'deals-'.now()->toDateString().'.xlsx');
    }

    /** @return array<string, mixed> */
    private function options(): array
    {
        return [
            'pipelines' => Pipeline::query()->with(['stages' => fn ($q) => $q->open()])->orderByDesc('is_default')->orderBy('name')->get(),
            'contacts' => $this->contactOptions(),
            'accounts' => $this->accountOptions(),
            'owners' => $this->userOptions(),
            'branches' => $this->branchOptions(),
            'currencies' => $this->currencyOptions(),
        ];
    }
}
