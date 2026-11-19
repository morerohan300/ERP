<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use Modules\CRM\Models\EmailTemplate;
use Modules\CRM\Models\LeadScoringRule;
use Modules\CRM\Models\LeadSource;
use Modules\CRM\Models\LeadStatus;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Models\SlaPolicy;
use Modules\CRM\Models\TicketCategory;

/**
 * Installs the starting data a company needs to use the CRM: lead sources and
 * statuses, a sales pipeline, ticket categories, SLA policies, scoring rules and
 * email templates. Safe to run repeatedly; it never overwrites or duplicates.
 */
class CrmSetupService
{
    private const BUSINESS_HOURS = [
        'timezone' => 'UTC',
        'days' => [
            'mon' => ['09:00', '17:00'],
            'tue' => ['09:00', '17:00'],
            'wed' => ['09:00', '17:00'],
            'thu' => ['09:00', '17:00'],
            'fri' => ['09:00', '17:00'],
        ],
    ];

    public function __construct(private readonly CurrentCompany $currentCompany) {}

    /** Install the defaults for a company, working inside that company's context. */
    public function install(Company $company): void
    {
        $this->currentCompany->runAs($company, function (): void {
            DB::transaction(function (): void {
                $this->leadSources();
                $this->leadStatuses();
                $this->pipeline();
                $this->ticketSetup();
                $this->scoringRules();
                $this->emailTemplates();
            });
        });
    }

    private function leadSources(): void
    {
        foreach (['Website', 'Referral', 'Cold call', 'Social media', 'Trade show', 'Email campaign', 'Partner'] as $name) {
            LeadSource::query()->firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }

    private function leadStatuses(): void
    {
        foreach ([
            ['New', 'slate', 1, false, false],
            ['Contacted', 'navy', 2, false, false],
            ['Qualified', 'gold', 3, false, false],
            ['Unqualified', 'danger', 4, false, true],
            ['Converted', 'success', 5, true, false],
        ] as [$name, $color, $order, $converted, $lost]) {
            LeadStatus::query()->firstOrCreate(['name' => $name], [
                'color' => $color,
                'sort_order' => $order,
                'is_converted' => $converted,
                'is_lost' => $lost,
            ]);
        }
    }

    private function pipeline(): void
    {
        if (Pipeline::query()->exists()) {
            return;
        }

        $pipeline = Pipeline::query()->create(['name' => 'Sales Pipeline', 'is_default' => true]);

        foreach ([
            ['Qualification', 10, false, false, 'slate'],
            ['Needs analysis', 25, false, false, 'navy'],
            ['Proposal', 50, false, false, 'gold'],
            ['Negotiation', 75, false, false, 'warning'],
            ['Closed won', 100, true, false, 'success'],
            ['Closed lost', 0, false, true, 'danger'],
        ] as $index => [$name, $probability, $won, $lost, $color]) {
            PipelineStage::query()->create([
                'pipeline_id' => $pipeline->getKey(),
                'name' => $name,
                'probability' => $probability,
                'sort_order' => $index + 1,
                'is_won' => $won,
                'is_lost' => $lost,
                'color' => $color,
            ]);
        }
    }

    private function ticketSetup(): void
    {
        foreach (['General', 'Billing', 'Technical', 'Feature request'] as $name) {
            TicketCategory::query()->firstOrCreate(['name' => $name]);
        }

        foreach ([
            ['Urgent', 'urgent', 30, 240, null],
            ['High', 'high', 60, 480, self::BUSINESS_HOURS],
            ['Medium', 'medium', 240, 1440, self::BUSINESS_HOURS],
            ['Low', 'low', 480, 4320, self::BUSINESS_HOURS],
        ] as [$name, $priority, $firstResponse, $resolution, $hours]) {
            SlaPolicy::query()->firstOrCreate(['name' => $name], [
                'priority' => $priority,
                'first_response_minutes' => $firstResponse,
                'resolution_minutes' => $resolution,
                'business_hours' => $hours,
                'is_active' => true,
            ]);
        }
    }

    private function scoringRules(): void
    {
        if (LeadScoringRule::query()->exists()) {
            return;
        }

        foreach ([
            ['email', 'filled', null, 10],
            ['phone', 'filled', null, 5],
            ['company_name', 'filled', null, 10],
            ['estimated_value', 'gt', '10000', 20],
            ['estimated_value', 'gt', '50000', 15],
        ] as [$field, $operator, $value, $points]) {
            LeadScoringRule::query()->create(['field' => $field, 'operator' => $operator, 'value' => $value, 'points' => $points, 'is_active' => true]);
        }
    }

    private function emailTemplates(): void
    {
        foreach ([
            [
                'Introduction',
                'Nice to meet you, {{ first_name }}',
                "<p>Hi {{ first_name }},</p><p>Thanks for your interest in {{ company }}. I'd love to learn more about what you are looking for.</p><p>Would you have 15 minutes this week for a quick call?</p><p>Best regards,<br>{{ sender_name }}</p>",
            ],
            [
                'Follow-up',
                'Following up, {{ first_name }}',
                '<p>Hi {{ first_name }},</p><p>I wanted to follow up on my previous message and see whether you have any questions.</p><p>Best regards,<br>{{ sender_name }}</p>',
            ],
            [
                'Newsletter',
                'News from {{ company }}',
                '<p>Hello {{ first_name }},</p><p>Here is what is new at {{ company }} this month.</p><p>— The {{ company }} team</p>',
            ],
        ] as [$name, $subject, $body]) {
            EmailTemplate::query()->firstOrCreate(['name' => $name], ['module' => 'crm', 'subject' => $subject, 'body' => $body, 'is_active' => true]);
        }
    }
}
