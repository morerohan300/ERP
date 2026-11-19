<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;
use Modules\CRM\Services\SegmentService;

class SegmentRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.segment');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueInCompany('segments', 'name', $this->route('segment')?->getKey())],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_dynamic' => ['boolean'],
            'rules' => ['nullable', 'array', 'max:20'],
            'rules.*.field' => ['required', Rule::in(array_keys(SegmentService::FIELDS))],
            'rules.*.operator' => ['required', Rule::in(array_keys(SegmentService::OPERATORS))],
            'rules.*.value' => ['nullable', 'string', 'max:255'],
            'member_ids' => ['nullable', 'array', 'max:5000'],
            'member_ids.*' => ['integer', $this->existsInCompany('contacts')],
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = $this->safe()->except(['member_ids']);

        $data['rules'] = collect($data['rules'] ?? [])
            ->filter(fn (array $rule) => in_array($rule['operator'] ?? '', ['filled', 'empty'], true) || filled($rule['value'] ?? null))
            ->values()
            ->all();

        return $data;
    }

    /** @return array<int, int> */
    public function memberIds(): array
    {
        return array_map('intval', (array) $this->validated('member_ids', []));
    }
}
