<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Modules\CRM\Models\SlaPolicy;
use Modules\CRM\Models\Ticket;

/**
 * Picks the SLA policy for a ticket's priority and works out its deadlines.
 */
class SlaService
{
    public function __construct(private readonly BusinessHours $hours) {}

    public function policyFor(string $priority): ?SlaPolicy
    {
        return SlaPolicy::query()->active()->where('priority', $priority)->orderBy('id')->first();
    }

    /**
     * (Re)calculate the ticket's deadlines from its creation time. A first-response
     * deadline is dropped once the ticket has been answered.
     */
    public function apply(Ticket $ticket): Ticket
    {
        $policy = $this->policyFor($ticket->priority);

        if ($policy === null) {
            $ticket->forceFill(['sla_policy_id' => null, 'first_response_due_at' => null, 'resolution_due_at' => null])->save();

            return $ticket;
        }

        $start = $ticket->created_at ?? now();
        $zone = config('app.timezone');

        $ticket->forceFill([
            'sla_policy_id' => $policy->getKey(),
            'first_response_due_at' => $ticket->first_responded_at === null
                ? $this->hours->addMinutes($start, $policy->first_response_minutes, $policy->business_hours)->setTimezone($zone)
                : $ticket->first_response_due_at,
            'resolution_due_at' => $this->hours->addMinutes($start, $policy->resolution_minutes, $policy->business_hours)->setTimezone($zone),
        ])->save();

        return $ticket;
    }
}
