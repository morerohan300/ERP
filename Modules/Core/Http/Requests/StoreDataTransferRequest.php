<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->input('direction') === 'export' ? 'core.user.export' : 'core.user.import');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'direction' => ['required', 'string', Rule::in(['import', 'export'])],
            'module' => ['required', 'string', 'max:40'],
            'entity' => ['required', 'string', 'max:60'],
            'format' => ['required', 'string', Rule::in(['xlsx', 'csv'])],
            'file' => ['required_if:direction,import', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }
}
