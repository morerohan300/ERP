<?php

declare(strict_types=1);

namespace Modules\CRM\Providers;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Modules\Core\Events\CompanyCreated;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Services\GlobalSearch;
use Modules\CRM\Console\CheckSlaCommand;
use Modules\CRM\Console\DispatchCampaignsCommand;
use Modules\CRM\Console\SendRemindersCommand;
use Modules\CRM\Console\SetupCommand;
use Modules\CRM\Events\LeadConverted;
use Modules\CRM\Http\Middleware\SetPortalCompany;
use Modules\CRM\Listeners\InstallCrmDefaults;
use Modules\CRM\Listeners\LogLeadConversion;
use Modules\CRM\Livewire;
use Modules\CRM\Models;
use Modules\CRM\Policies;
use Modules\CRM\Services\Sms\SmsGateway;

class CRMServiceProvider extends ModuleServiceProvider
{
    protected string $moduleKey = 'crm';

    protected string $moduleName = 'CRM';

    protected array $policies = [
        Models\Lead::class => Policies\LeadPolicy::class,
        Models\Contact::class => Policies\ContactPolicy::class,
        Models\CrmAccount::class => Policies\AccountPolicy::class,
        Models\Deal::class => Policies\DealPolicy::class,
        Models\Pipeline::class => Policies\PipelinePolicy::class,
        Models\Activity::class => Policies\ActivityPolicy::class,
        Models\EmailTemplate::class => Policies\EmailTemplatePolicy::class,
        Models\Segment::class => Policies\SegmentPolicy::class,
        Models\Campaign::class => Policies\CampaignPolicy::class,
        Models\Ticket::class => Policies\TicketPolicy::class,
        Models\SlaPolicy::class => Policies\SlaPolicyPolicy::class,
        Models\LeadSource::class => Policies\CrmSettingsPolicy::class,
        Models\LeadStatus::class => Policies\CrmSettingsPolicy::class,
        Models\LeadScoringRule::class => Policies\CrmSettingsPolicy::class,
        Models\TicketCategory::class => Policies\CrmSettingsPolicy::class,
    ];

    protected array $listen = [
        CompanyCreated::class => [InstallCrmDefaults::class],
        LeadConverted::class => [LogLeadConversion::class],
    ];

    protected array $livewire = [
        'leads-table' => Livewire\LeadsTable::class,
        'contacts-table' => Livewire\ContactsTable::class,
        'accounts-table' => Livewire\AccountsTable::class,
        'deals-board' => Livewire\DealsBoard::class,
        'activities-table' => Livewire\ActivitiesTable::class,
        'activity-calendar' => Livewire\ActivityCalendar::class,
        'tickets-table' => Livewire\TicketsTable::class,
        'campaigns-table' => Livewire\CampaignsTable::class,
        'lead-setup' => Livewire\LeadSetup::class,
    ];

    public function register(): void
    {
        parent::register();

        // The customer portal signs in through its own guard so portal customers can never be staff users.
        config([
            'auth.guards.portal' => ['driver' => 'session', 'provider' => 'portal_users'],
            'auth.providers.portal_users' => ['driver' => 'eloquent', 'model' => Models\PortalUser::class],
        ]);

        $this->app->bind(SmsGateway::class, fn ($app) => $app->make((string) config('crm.sms_gateway')));
    }

    public function boot(): void
    {
        parent::boot();

        Relation::morphMap([
            'lead' => Models\Lead::class,
            'contact' => Models\Contact::class,
            'crm_account' => Models\CrmAccount::class,
            'deal' => Models\Deal::class,
            'ticket' => Models\Ticket::class,
            'activity' => Models\Activity::class,
            'campaign' => Models\Campaign::class,
            'segment' => Models\Segment::class,
            'email_template' => Models\EmailTemplate::class,
            'pipeline' => Models\Pipeline::class,
        ]);

        CustomFieldService::registerEntity('lead', __('Leads'));
        CustomFieldService::registerEntity('contact', __('Contacts'));
        CustomFieldService::registerEntity('crm_account', __('CRM accounts'));
        CustomFieldService::registerEntity('deal', __('Deals'));

        $this->registerSearchProviders();

        if ($this->app->runningInConsole()) {
            $this->commands([SetupCommand::class, DispatchCampaignsCommand::class, SendRemindersCommand::class, CheckSlaCommand::class]);
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('crm:dispatch-campaigns')->everyMinute()->withoutOverlapping();
            $schedule->command('crm:send-reminders')->everyFiveMinutes()->withoutOverlapping();
            $schedule->command('crm:check-sla')->everyFifteenMinutes()->withoutOverlapping();
        });
    }

    /** Staff routes come from the base class; tracking pixels and the customer portal are public. */
    protected function registerRoutes(): void
    {
        parent::registerRoutes();

        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->prefix('crm/t')
            ->name('crm.track.')
            ->group($this->modulePath('Routes/public.php'));

        Route::middleware(['web', SetPortalCompany::class])
            ->prefix('portal/{company}')
            ->name('crm.portal.')
            ->group($this->modulePath('Routes/portal.php'));
    }

    private function registerSearchProviders(): void
    {
        GlobalSearch::register('crm', 'Leads', 'crm.lead.view', fn (string $term, int $limit) => Models\Lead::query()
            ->where(fn ($q) => $q->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('number', 'like', "%{$term}%"))
            ->limit($limit)->get()
            ->map(fn (Models\Lead $l) => ['title' => $l->full_name, 'subtitle' => trim($l->number.' · '.($l->company_name ?? '')), 'url' => route('crm.leads.show', $l)]));

        GlobalSearch::register('crm', 'Contacts', 'crm.contact.view', fn (string $term, int $limit) => Models\Contact::query()
            ->where(fn ($q) => $q->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->limit($limit)->get()
            ->map(fn (Models\Contact $c) => ['title' => $c->full_name, 'subtitle' => $c->email, 'url' => route('crm.contacts.show', $c)]));

        GlobalSearch::register('crm', 'Accounts', 'crm.account.view', fn (string $term, int $limit) => Models\CrmAccount::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->limit($limit)->get()
            ->map(fn (Models\CrmAccount $a) => ['title' => $a->name, 'subtitle' => $a->industry, 'url' => route('crm.accounts.show', $a)]));

        GlobalSearch::register('crm', 'Deals', 'crm.deal.view', fn (string $term, int $limit) => Models\Deal::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('number', 'like', "%{$term}%"))
            ->limit($limit)->get()
            ->map(fn (Models\Deal $d) => ['title' => $d->title, 'subtitle' => $d->number, 'url' => route('crm.deals.show', $d)]));

        GlobalSearch::register('crm', 'Tickets', 'crm.ticket.view', fn (string $term, int $limit) => Models\Ticket::query()
            ->where(fn ($q) => $q->where('subject', 'like', "%{$term}%")->orWhere('number', 'like', "%{$term}%"))
            ->limit($limit)->get()
            ->map(fn (Models\Ticket $t) => ['title' => $t->subject, 'subtitle' => $t->number, 'url' => route('crm.tickets.show', $t)]));
    }
}
