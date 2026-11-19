<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Exports\QueryExport;
use Modules\CRM\Http\Requests\ContactRequest;
use Modules\CRM\Http\Requests\PortalUserRequest;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\EmailTemplate;
use Modules\CRM\Models\PortalUser;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ContactController extends Controller
{
    public function __construct(private readonly CustomFieldService $customFields)
    {
        $this->authorizeResource(Contact::class, 'contact');
    }

    public function index(): View
    {
        return view('crm::contacts.index');
    }

    public function create(): View
    {
        return view('crm::contacts.create', ['contact' => null, ...$this->options(), 'preset' => request()->only('crm_account_id')]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $contact = Contact::query()->create($request->payload());
        $this->customFields->save($contact, $request->customFields());

        return $this->status('crm.contacts.show', __('Contact created.'), $contact);
    }

    public function show(Contact $contact): View
    {
        $contact->load(['account', 'owner', 'deals.stage', 'tickets', 'activities.owner', 'emailLogs', 'portalUser']);

        return view('crm::contacts.show', [
            'contact' => $contact,
            'templates' => EmailTemplate::query()->active()->orderBy('name')->get(['id', 'name', 'subject', 'body']),
        ]);
    }

    public function edit(Contact $contact): View
    {
        return view('crm::contacts.edit', ['contact' => $contact, ...$this->options(), 'preset' => []]);
    }

    public function update(ContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->fill($request->payload())->save();
        $this->customFields->save($contact, $request->customFields());

        return $this->status('crm.contacts.show', __('Contact updated.'), $contact);
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return $this->status('crm.contacts.index', __('Contact deleted.'));
    }

    /** Create (or reset the password of) the contact's support-portal login. */
    public function grantPortalAccess(PortalUserRequest $request, Contact $contact): RedirectResponse
    {
        if (blank($contact->email)) {
            throw ValidationException::withMessages(['password' => __('Add an email address to this contact before giving portal access.')]);
        }

        $portalUser = PortalUser::query()->firstOrNew(['email' => $contact->email]);
        $portalUser->fill([
            'name' => $contact->full_name,
            'contact_id' => $contact->getKey(),
            'password' => $request->validated('password'),
            'is_active' => true,
        ])->save();

        return $this->status('crm.contacts.show', __('Portal access saved.'), $contact);
    }

    public function revokePortalAccess(Contact $contact): RedirectResponse
    {
        Gate::authorize('managePortal', $contact);

        PortalUser::query()->where('contact_id', $contact->getKey())->delete();

        return $this->status('crm.contacts.show', __('Portal access removed.'), $contact);
    }

    public function export(): BinaryFileResponse
    {
        Gate::authorize('export', Contact::class);

        return Excel::download(new QueryExport(
            Contact::query()->with(['account', 'owner'])->orderBy('id'),
            [__('First name'), __('Last name'), __('Job title'), __('Account'), __('Email'), __('Phone'), __('Mobile'), __('Owner'), __('Opted out of email'), __('Created')],
            fn (Contact $c): array => [
                $c->first_name, $c->last_name, $c->job_title, $c->account?->name, $c->email, $c->phone, $c->mobile,
                $c->owner?->name, $c->do_not_email ? __('Yes') : __('No'), $c->created_at?->toDateString(),
            ],
        ), 'contacts-'.now()->toDateString().'.xlsx');
    }

    /** @return array<string, mixed> */
    private function options(): array
    {
        return ['accounts' => $this->accountOptions(), 'owners' => $this->userOptions()];
    }
}
