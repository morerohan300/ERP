<?php

declare(strict_types=1);

namespace Modules\CRM\Listeners;

use Modules\CRM\Events\LeadConverted;
use Modules\CRM\Models\Activity;

/** Leaves a note on the new contact and deal recording where they came from. */
class LogLeadConversion
{
    public function handle(LeadConverted $event): void
    {
        $subject = __('Converted from lead :number', ['number' => $event->lead->number]);

        foreach (array_filter([$event->contact, $event->deal]) as $record) {
            Activity::query()->create([
                'type' => 'note',
                'subject' => $subject,
                'related_type' => $record->getMorphClass(),
                'related_id' => $record->getKey(),
                'owner_id' => $event->lead->owner_id,
            ])->forceFill(['completed_at' => now()])->save();
        }
    }
}
