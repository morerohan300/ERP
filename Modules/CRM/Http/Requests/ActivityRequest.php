<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;
use Modules\CRM\Models\Activity;
use Modules\CRM\Support\UserTime;

class ActivityRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.activity');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(Activity::TYPES))],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'related_type' => ['nullable', Rule::in(Activity::RELATABLE), 'required_with:related_uuid'],
            'related_uuid' => ['nullable', 'uuid', 'required_with:related_type'],
            'owner_id' => ['nullable', $this->companyUser()],
            'due_at' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'outcome' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', Rule::in(array_keys(Activity::PRIORITIES))],
            'reminder_at' => ['nullable', 'date'],
            'contact_ids' => ['nullable', 'array', 'max:50'],
            'contact_ids.*' => ['integer', $this->existsInCompany('contacts')],
        ];
    }

    /**
     * Activity attributes with the date fields converted from the user's timezone to storage time.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->safe()->except(['related_type', 'related_uuid', 'contact_ids']);

        foreach (['due_at', 'starts_at', 'ends_at', 'reminder_at'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = UserTime::fromInput($data[$field]);
            }
        }

        $data['priority'] ??= 'normal';

        return $data;
    }

    /** @return array<int, int> */
    public function contactIds(): array
    {
        return array_map('intval', (array) $this->validated('contact_ids', []));
    }
}
