<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;
use Modules\CRM\Models\SlaPolicy;
use Modules\CRM\Models\Ticket;

class SlaPolicyRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.sla_policy');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', $this->uniqueInCompany('sla_policies', 'name', $this->route('sla_policy')?->getKey())],
            'priority' => ['required', Rule::in(array_keys(Ticket::PRIORITIES))],
            'first_response_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'resolution_minutes' => ['required', 'integer', 'gte:first_response_minutes', 'max:525600'],
            'is_active' => ['boolean'],
            'business_hours_enabled' => ['boolean'],
            'timezone' => ['nullable', 'timezone'],
        ];

        foreach (array_keys(SlaPolicy::DAYS) as $day) {
            $rules["hours.{$day}.open"] = ['nullable', 'date_format:H:i'];
            $rules["hours.{$day}.close"] = ['nullable', 'date_format:H:i', "after:hours.{$day}.open"];
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = $this->safe()->only(['name', 'priority', 'first_response_minutes', 'resolution_minutes', 'is_active']);
        $data['business_hours'] = $this->businessHours();

        return $data;
    }

    /**
     * The 24/7 schedule is stored as null; otherwise the open days with their hours.
     *
     * @return array{timezone: string, days: array<string, array{0: string, 1: string}>}|null
     */
    private function businessHours(): ?array
    {
        if (! $this->boolean('business_hours_enabled')) {
            return null;
        }

        $days = [];
        foreach (array_keys(SlaPolicy::DAYS) as $day) {
            $open = $this->validated("hours.{$day}.open");
            $close = $this->validated("hours.{$day}.close");

            if ($open && $close) {
                $days[$day] = [$open, $close];
            }
        }

        return $days === [] ? null : ['timezone' => $this->validated('timezone') ?: 'UTC', 'days' => $days];
    }
}
