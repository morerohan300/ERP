<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\EmailTemplate;
use Modules\CRM\Models\Lead;

/**
 * Renders email templates by replacing merge tags such as {{ first_name }}.
 * Values are HTML-escaped; unknown tags render as an empty string.
 */
class EmailTemplateService
{
    /** @var array<string, string> tag => description, shown in the template editor */
    public const TAGS = [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'full_name' => 'Full name',
        'email' => 'Email address',
        'company_name' => 'Customer company',
        'sender_name' => 'Your name',
        'company' => 'Your company',
        'today' => 'Today\'s date',
    ];

    /**
     * @param  array<string, string|null>  $data
     * @return array{subject: string, body: string}
     */
    public function render(EmailTemplate|string $subject, ?string $body = null, array $data = []): array
    {
        if ($subject instanceof EmailTemplate) {
            $body = $subject->body;
            $subject = $subject->subject;
        }

        return [
            'subject' => $this->replace($subject, $data, plain: true),
            'body' => $this->replace((string) $body, $data),
        ];
    }

    /**
     * Merge-tag values for a lead or contact.
     *
     * @return array<string, string|null>
     */
    public function dataFor(?Model $recipient, ?User $sender = null): array
    {
        $company = app(CurrentCompany::class)->get();

        $data = [
            'sender_name' => $sender?->name,
            'company' => $company?->name,
            'today' => now()->format($company?->date_format ?: 'd M Y'),
        ];

        if ($recipient instanceof Contact) {
            $data += [
                'first_name' => $recipient->first_name,
                'last_name' => $recipient->last_name,
                'full_name' => $recipient->full_name,
                'email' => $recipient->email,
                'company_name' => $recipient->account?->name,
            ];
        } elseif ($recipient instanceof Lead) {
            $data += [
                'first_name' => $recipient->first_name,
                'last_name' => $recipient->last_name,
                'full_name' => $recipient->full_name,
                'email' => $recipient->email,
                'company_name' => $recipient->company_name,
            ];
        }

        return $data;
    }

    /** @param array<string, string|null> $data */
    private function replace(string $text, array $data, bool $plain = false): string
    {
        return (string) preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/i', function (array $match) use ($data, $plain): string {
            $value = (string) ($data[strtolower($match[1])] ?? '');

            return $plain ? $value : e($value);
        }, $text);
    }
}
