<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CRM\Http\Requests\Concerns\ChecksCompanyRules;
use Modules\CRM\Support\UserTime;

class CampaignRequest extends FormRequest
{
    use ChecksCompanyRules;

    public function authorize(): bool
    {
        return $this->canWrite('crm.campaign');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:email,sms'],
            'segment_id' => ['required', $this->existsInCompany('segments')],
            'email_template_id' => ['nullable', 'required_if:type,email', $this->existsInCompany('email_templates')],
            'sms_body' => ['nullable', 'required_if:type,sms', 'string', 'max:480'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = $this->safe()->except('scheduled_at');

        // Fields that do not apply to the chosen type are cleared.
        if ($data['type'] === 'sms') {
            $data['email_template_id'] = null;
        } else {
            $data['sms_body'] = null;
        }

        return $data;
    }

    /** The requested send time converted from the user's timezone, or null for "save as draft". */
    public function scheduledAt(): ?\Illuminate\Support\Carbon
    {
        return UserTime::fromInput($this->validated('scheduled_at'));
    }
}
