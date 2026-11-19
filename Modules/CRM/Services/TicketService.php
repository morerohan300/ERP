<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Modules\CRM\Events\TicketCreated;
use Modules\CRM\Mail\TicketReplyMail;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Ticket;
use Modules\CRM\Models\TicketReply;
use Modules\CRM\Notifications\TicketAssignedNotification;

/**
 * Support ticket lifecycle: creation with SLA deadlines, replies, status changes and assignment.
 */
class TicketService
{
    public function __construct(private readonly SlaService $sla) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?User $by = null): Ticket
    {
        return DB::transaction(function () use ($data, $by): Ticket {
            $data['status'] = 'open';
            $data['priority'] ??= 'medium';
            $data['channel'] ??= 'web';

            $ticket = Ticket::query()->create($data);
            $this->sla->apply($ticket);

            if ($ticket->assigned_to !== null && $ticket->assigned_to !== $by?->getKey()) {
                $this->notifyAssignee($ticket);
            }

            TicketCreated::dispatch($ticket);

            return $ticket->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Ticket $ticket, array $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $data): Ticket {
            $status = $data['status'] ?? $ticket->status;
            unset($data['status']);

            $ticket->fill($data);
            $assigneeChanged = $ticket->isDirty('assigned_to') && $ticket->assigned_to !== null;
            $priorityChanged = $ticket->isDirty('priority');
            $ticket->save();

            if ($priorityChanged) {
                $this->sla->apply($ticket);
            }

            $this->changeStatus($ticket, $status);

            if ($assigneeChanged) {
                $this->notifyAssignee($ticket);
            }

            return $ticket->refresh();
        });
    }

    public function changeStatus(Ticket $ticket, string $status): Ticket
    {
        if ($ticket->status === $status) {
            return $ticket;
        }

        $closing = in_array($status, ['resolved', 'closed'], true);

        $ticket->forceFill([
            'status' => $status,
            'resolved_at' => $closing ? ($ticket->resolved_at ?? now()) : null,
        ])->save();

        return $ticket;
    }

    /**
     * Add a reply from a staff member ($user) or the customer ($contact).
     *
     * A public staff reply is the ticket's first response and moves it to "pending".
     * A customer reply reopens a pending, on-hold or resolved ticket.
     */
    public function addReply(Ticket $ticket, string $body, ?User $user = null, ?Contact $contact = null, bool $internal = false): TicketReply
    {
        return DB::transaction(function () use ($ticket, $body, $user, $contact, $internal): TicketReply {
            $reply = TicketReply::query()->create([
                'ticket_id' => $ticket->getKey(),
                'user_id' => $user?->getKey(),
                'contact_id' => $user === null ? $contact?->getKey() : null,
                'body' => $body,
                'is_internal' => $user !== null && $internal,
            ]);

            if ($user !== null && ! $internal) {
                if ($ticket->first_responded_at === null) {
                    $ticket->forceFill(['first_responded_at' => now()])->save();
                }

                if ($ticket->status === 'open') {
                    $this->changeStatus($ticket, 'pending');
                }

                $this->emailCustomer($ticket, $reply, $user);
            } elseif ($user === null && in_array($ticket->status, ['pending', 'on_hold', 'resolved'], true)) {
                $this->changeStatus($ticket, 'open');
            }

            return $reply;
        });
    }

    private function notifyAssignee(Ticket $ticket): void
    {
        $ticket->assignee?->notify(TicketAssignedNotification::for($ticket));
    }

    private function emailCustomer(Ticket $ticket, TicketReply $reply, User $agent): void
    {
        $email = $ticket->contact?->email;

        if ($email === null || $email === '' || $ticket->contact?->do_not_email) {
            return;
        }

        $portalUrl = $ticket->contact->portalUser()->exists() && $ticket->company !== null
            ? URL::route('crm.portal.tickets.show', ['company' => $ticket->company->uuid, 'ticket' => $ticket->uuid])
            : null;

        Mail::to($email)->send(new TicketReplyMail($ticket->number, $ticket->subject, $reply->body, $agent->name, $portalUrl));
    }
}
