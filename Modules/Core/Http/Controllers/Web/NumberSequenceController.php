<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\UpdateNumberSequenceRequest;
use Modules\Core\Models\NumberSequence;

final class NumberSequenceController extends Controller
{
    public function index(): View
    {
        return view('core::numbering.index', [
            'sequences' => NumberSequence::query()->orderBy('document_type')->get(),
            'defaults' => config('numbering.defaults'),
        ]);
    }

    public function update(UpdateNumberSequenceRequest $request, NumberSequence $numberSequence): RedirectResponse
    {
        $numberSequence->update($request->validated());

        return $this->status('numbering.index', __('Number sequence updated.'));
    }
}
