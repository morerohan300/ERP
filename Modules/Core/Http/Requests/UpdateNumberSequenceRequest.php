<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNumberSequenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('core.numbering.edit');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'prefix' => ['required', 'string', 'max:20'],
            'format' => ['required', 'string', 'max:60'],
            'padding' => ['required', 'integer', 'min:1', 'max:10'],
            'reset_period' => ['required', 'string', Rule::in(['never', 'yearly', 'monthly'])],
        ];
    }
}
