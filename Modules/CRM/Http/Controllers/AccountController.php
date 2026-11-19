<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Exports\QueryExport;
use Modules\CRM\Http\Requests\AccountRequest;
use Modules\CRM\Models\CrmAccount;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AccountController extends Controller
{
    public function __construct(private readonly CustomFieldService $customFields)
    {
        $this->authorizeResource(CrmAccount::class, 'account');
    }

    public function index(): View
    {
        return view('crm::accounts.index');
    }

    public function create(): View
    {
        return view('crm::accounts.create', ['account' => null, ...$this->options()]);
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        $account = CrmAccount::query()->create($request->payload());
        $this->customFields->save($account, $request->customFields());

        return $this->status('crm.accounts.show', __('Account created.'), $account);
    }

    public function show(CrmAccount $account): View
    {
        $account->load(['owner', 'parent', 'children', 'contacts', 'deals.stage', 'activities.owner']);

        return view('crm::accounts.show', ['account' => $account]);
    }

    public function edit(CrmAccount $account): View
    {
        return view('crm::accounts.edit', ['account' => $account, ...$this->options($account)]);
    }

    public function update(AccountRequest $request, CrmAccount $account): RedirectResponse
    {
        $account->fill($request->payload())->save();
        $this->customFields->save($account, $request->customFields());

        return $this->status('crm.accounts.show', __('Account updated.'), $account);
    }

    public function destroy(CrmAccount $account): RedirectResponse
    {
        $account->delete();

        return $this->status('crm.accounts.index', __('Account deleted.'));
    }

    public function export(): BinaryFileResponse
    {
        Gate::authorize('export', CrmAccount::class);

        return Excel::download(new QueryExport(
            CrmAccount::query()->with('owner')->withCount(['contacts', 'deals'])->orderBy('id'),
            [__('Name'), __('Legal name'), __('Industry'), __('Email'), __('Phone'), __('Website'), __('Employees'), __('Annual revenue'), __('Contacts'), __('Deals'), __('Owner'), __('Created')],
            fn (CrmAccount $a): array => [
                $a->name, $a->legal_name, $a->industry, $a->email, $a->phone, $a->website, $a->employee_count,
                $a->annual_revenue, $a->contacts_count, $a->deals_count, $a->owner?->name, $a->created_at?->toDateString(),
            ],
        ), 'accounts-'.now()->toDateString().'.xlsx');
    }

    /** @return array<string, mixed> */
    private function options(?CrmAccount $except = null): array
    {
        return [
            'owners' => $this->userOptions(),
            'parents' => array_diff_key($this->accountOptions(), $except ? [$except->getKey() => true] : []),
        ];
    }
}
