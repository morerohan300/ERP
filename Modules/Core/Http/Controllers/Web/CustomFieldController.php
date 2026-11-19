<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreCustomFieldRequest;
use Modules\Core\Http\Requests\UpdateCustomFieldRequest;
use Modules\Core\Models\CustomField;
use Modules\Core\Services\CustomFieldService;

final class CustomFieldController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CustomField::class, 'custom_field');
    }

    public function index(): View
    {
        return view('core::custom-fields.index', [
            'fields' => CustomField::query()->orderBy('entity_type')->orderBy('sort_order')->get(),
            'entities' => CustomFieldService::entities(),
        ]);
    }

    public function create(): View
    {
        return view('core::custom-fields.create', [
            'entities' => CustomFieldService::entities(),
            'types' => CustomField::TYPES,
        ]);
    }

    public function store(StoreCustomFieldRequest $request): RedirectResponse
    {
        CustomField::query()->create($request->validated());

        return $this->status('custom-fields.index', __('Custom field created.'));
    }

    public function show(CustomField $customField): RedirectResponse
    {
        return redirect()->route('custom-fields.edit', $customField);
    }

    public function edit(CustomField $customField): View
    {
        return view('core::custom-fields.edit', [
            'field' => $customField,
            'entities' => CustomFieldService::entities(),
            'types' => CustomField::TYPES,
        ]);
    }

    public function update(UpdateCustomFieldRequest $request, CustomField $customField): RedirectResponse
    {
        $customField->update($request->validated());

        return $this->status('custom-fields.index', __('Custom field updated.'));
    }

    public function destroy(CustomField $customField): RedirectResponse
    {
        $customField->delete();

        return $this->status('custom-fields.index', __('Custom field deleted.'));
    }
}
