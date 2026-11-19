<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\ValidationException;
use Modules\CRM\Models\Activity;
use Modules\CRM\Models\Contact;

/**
 * Creates and completes activities (calls, meetings, tasks, notes, emails).
 */
class ActivityService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $contactIds  contacts taking part
     */
    public function create(array $data, User $by, ?Model $related = null, array $contactIds = []): Activity
    {
        if ($related !== null) {
            $data['related_type'] = $related->getMorphClass();
            $data['related_id'] = $related->getKey();
        }

        $data['owner_id'] ??= $by->getKey();

        $activity = Activity::query()->create($data);

        if ($data['type'] === 'note') {
            $activity->forceFill(['completed_at' => now()])->save();
        }

        $this->syncParticipants($activity, $contactIds);

        return $activity->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>|null  $contactIds
     */
    public function update(Activity $activity, array $data, ?array $contactIds = null): Activity
    {
        $activity->fill($data)->save();

        if ($contactIds !== null) {
            $this->syncParticipants($activity, $contactIds);
        }

        // A changed reminder time must be able to fire again.
        if ($activity->wasChanged('reminder_at')) {
            $activity->forceFill(['reminder_sent_at' => null])->save();
        }

        return $activity->refresh();
    }

    public function complete(Activity $activity, ?string $outcome = null): Activity
    {
        $activity->forceFill([
            'completed_at' => $activity->completed_at ?? now(),
            'outcome' => $outcome ?? $activity->outcome,
        ])->save();

        return $activity;
    }

    public function reopen(Activity $activity): Activity
    {
        $activity->forceFill(['completed_at' => null])->save();

        return $activity;
    }

    /**
     * Resolve the record an activity is attached to from a morph alias and uuid.
     *
     * @throws ValidationException when the alias cannot carry activities
     */
    public function resolveRelated(string $alias, string $uuid): Model
    {
        $class = Relation::getMorphedModel($alias);

        if (! in_array($alias, Activity::RELATABLE, true) || $class === null) {
            throw ValidationException::withMessages(['related_type' => __('Activities cannot be attached to this record type.')]);
        }

        return $class::query()->where('uuid', $uuid)->firstOrFail();
    }

    /** @param array<int, int> $contactIds */
    private function syncParticipants(Activity $activity, array $contactIds): void
    {
        $valid = Contact::query()->whereIn('id', $contactIds)->pluck('id')->all();

        $activity->participants()->whereNotIn('participant_id', $valid)->where('participant_type', 'contact')->delete();

        foreach ($valid as $id) {
            $activity->participants()->firstOrCreate(['participant_type' => 'contact', 'participant_id' => $id]);
        }
    }
}
