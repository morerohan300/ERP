<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\CRM\Http\Requests\PipelineRequest;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Services\PipelineService;

final class PipelineController extends Controller
{
    public function __construct(private readonly PipelineService $pipelines)
    {
        $this->authorizeResource(Pipeline::class, 'pipeline');
    }

    public function index(): View
    {
        return view('crm::pipelines.index', [
            'pipelines' => Pipeline::query()->withCount(['stages', 'deals'])->orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('crm::pipelines.create', ['pipeline' => null]);
    }

    public function store(PipelineRequest $request): RedirectResponse
    {
        $this->pipelines->save(null, $request->validated(), $request->validated('stages'));

        return $this->status('crm.pipelines.index', __('Pipeline created.'));
    }

    public function show(Pipeline $pipeline): RedirectResponse
    {
        return redirect()->route('crm.pipelines.edit', $pipeline);
    }

    public function edit(Pipeline $pipeline): View
    {
        return view('crm::pipelines.edit', ['pipeline' => $pipeline->load('stages')]);
    }

    public function update(PipelineRequest $request, Pipeline $pipeline): RedirectResponse
    {
        $this->pipelines->save($pipeline, $request->validated(), $request->validated('stages'));

        return $this->status('crm.pipelines.index', __('Pipeline updated.'));
    }

    public function destroy(Pipeline $pipeline): RedirectResponse
    {
        $pipeline->stages()->delete();
        $pipeline->delete();

        return $this->status('crm.pipelines.index', __('Pipeline deleted.'));
    }
}
