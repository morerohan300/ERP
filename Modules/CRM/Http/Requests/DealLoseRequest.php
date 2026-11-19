<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DealLoseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('deal'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:255']];
    }
}
