<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\CRM\Http\Requests\ActivityRequest;
use Modules\CRM\Models\Activity;
use Modules\CRM\Services\ActivityService;

final class ActivityController extends Controller
{
    public function __construct(private readonly ActivityService $activities)
    {
        $this->authorizeResource(Activity::class, 'activity');
    }

    public function index(): View
    {
        return view('crm::activities.index');
    }

    public function calendar(): View
    {
        Gate::authorize('viewAny', Activity::class);

        return view('crm::activities.calendar');
    }

    public function create(Request $request): View
    {
        return view('crm::activities.create', [
            'activity' => null,
            'preset' => $request->only(['type', 'related_type', 'related_uuid']),
            'owners' => $this->userOptions(),
            'contacts' => $this->contactOptions(),
        ]);
    }

    public function store(ActivityRequest $request): RedirectResponse
    {
        $related = $request->filled('related_uuid')
            ? $this->activities->resolveRelated((string) $request->validated('related_type'), (string) $request->validated('related_uuid'))
            : null;

        $this->activities->create($request->payload(), $request->user(), $related, $request->contactIds());

        return $request->boolean('stay')
            ? back()->with('status', __('Activity added.'))
            : $this->status('crm.activities.index', __('Activity created.'));
    }

    public function edit(Activity $activity): View
    {
        $activity->load('participants');

        return view('crm::activities.edit', [
            'activity' => $activity,
            'preset' => [],
            'owners' => $this->userOptions(),
            'contacts' => $this->contactOptions(),
        ]);
    }

    public function update(ActivityRequest $request, Activity $activity): RedirectResponse
    {
        $this->activities->update($activity, $request->payload(), $request->contactIds());

        return $this->status('crm.activities.index', __('Activity updated.'));
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('status', __('Activity deleted.'));
    }

    public function complete(Request $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('update', $activity);
        $this->activities->complete($activity, $request->string('outcome')->limit(255, '')->toString() ?: null);

        return back()->with('status', __('Activity completed.'));
    }

    public function reopen(Activity $activity): RedirectResponse
    {
        Gate::authorize('update', $activity);
        $this->activities->reopen($activity);

        return back()->with('status', __('Activity reopened.'));
    }
}
