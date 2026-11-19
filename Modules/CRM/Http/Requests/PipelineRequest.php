<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;

class PipelineRequest extends FormRequest
{
    use ChecksCompanyRules;

    public const COLORS = ['slate', 'navy', 'gold', 'success', 'warning', 'danger'];

    public function authorize(): bool
    {
        return $this->canWrite('crm.pipeline');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueInCompany('pipelines', 'name', $this->route('pipeline')?->getKey())],
            'is_default' => ['boolean'],
            'stages' => ['required', 'array', 'min:2', 'max:20'],
            'stages.*.id' => ['nullable', 'integer'],
            'stages.*.name' => ['required', 'string', 'max:100'],
            'stages.*.probability' => ['required', 'integer', 'between:0,100'],
            'stages.*.color' => ['nullable', Rule::in(self::COLORS)],
            'stages.*.is_won' => ['boolean'],
            'stages.*.is_lost' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $stages = collect($this->input('stages', []));

            if ($stages->contains(fn (array $s) => ! empty($s['is_won']) && ! empty($s['is_lost']))) {
                $validator->errors()->add('stages', __('A stage cannot be both won and lost.'));
            }

            if ($stages->reject(fn (array $s) => ! empty($s['is_won']) || ! empty($s['is_lost']))->isEmpty()) {
                $validator->errors()->add('stages', __('A pipeline needs at least one open stage.'));
            }
        });
    }
}
