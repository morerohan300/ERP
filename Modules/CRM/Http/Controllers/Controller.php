<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Modules\Core\Models\Branch;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\CrmAccount;

/**
 * Base class for CRM web controllers: authorization, flash helpers and the
 * dropdown option lists shared by the forms.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    protected function status(string $route, string $message, mixed $parameter = null): RedirectResponse
    {
        return redirect()->route($route, $parameter)->with('status', $message);
    }

    /** @return array<int, string> id => name of every member of the active company */
    protected function userOptions(): array
    {
        return User::query()->inCurrentCompany()->orderBy('name')->pluck('name', 'id')->all();
    }

    /** @return array<int, string> */
    protected function branchOptions(): array
    {
        return Branch::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
    }

    /** @return array<string, string> code => code */
    protected function currencyOptions(): array
    {
        return app(CurrentCompany::class)->get()?->currencies()->pluck('code', 'code')->all() ?? [];
    }

    /** @return array<int, string> */
    protected function accountOptions(): array
    {
        return CrmAccount::query()->orderBy('name')->limit(500)->pluck('name', 'id')->all();
    }

    /** @return array<int, string> id => full name */
    protected function contactOptions(): array
    {
        return Contact::query()->orderBy('first_name')->orderBy('last_name')->limit(500)->get()
            ->mapWithKeys(fn (Contact $contact) => [$contact->getKey() => trim($contact->full_name.($contact->email ? " ({$contact->email})" : ''))])
            ->all();
    }
}
