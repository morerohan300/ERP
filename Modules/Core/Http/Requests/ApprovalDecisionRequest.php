<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('act', $this->route('approval_request'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['comment' => ['nullable', 'string', 'max:2000']];
    }
}
