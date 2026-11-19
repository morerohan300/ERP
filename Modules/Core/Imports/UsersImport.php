<?php

declare(strict_types=1);

namespace Modules\Core\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Core\Models\DataTransfer;
use Modules\Core\Services\UserService;
use Spatie\Permission\Models\Role;

/**
 * Imports users into the current company from a sheet with the columns
 * name, email, phone, role. Rows that fail validation are reported back
 * on the DataTransfer record; valid rows are created.
 */
class UsersImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private readonly DataTransfer $transfer,
        private readonly UserService $users,
    ) {}

    /** @param Collection<int, Collection<string, mixed>> $rows */
    public function collection(Collection $rows): void
    {
        $roles = Role::query()->where('company_id', $this->transfer->company_id)->pluck('name')->all();
        $errors = [];
        $processed = 0;
        $failed = 0;

        $this->transfer->update(['status' => 'processing', 'total_rows' => $rows->count()]);

        foreach ($rows as $index => $row) {
            $line = $index + 2; // heading row + 1-based
            $data = [
                'name' => trim((string) ($row['name'] ?? '')),
                'email' => mb_strtolower(trim((string) ($row['email'] ?? ''))),
                'phone' => trim((string) ($row['phone'] ?? '')) ?: null,
                'role' => trim((string) ($row['role'] ?? '')) ?: null,
            ];

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:40'],
                'role' => ['nullable', 'in:'.implode(',', $roles)],
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = __('Row :line: :errors', ['line' => $line, 'errors' => implode(' ', $validator->errors()->all())]);

                continue;
            }

            try {
                $existing = User::query()->where('email', $data['email'])->first();
                if ($existing !== null && $existing->belongsToCompany($this->transfer->company_id)) {
                    throw new \RuntimeException(__('already a member'));
                }

                $this->users->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'roles' => $data['role'] ? [$data['role']] : [],
                ]);
                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = __('Row :line: :errors', ['line' => $line, 'errors' => $e->getMessage()]);
            }
        }

        $this->transfer->update([
            'status' => 'completed',
            'processed_rows' => $processed,
            'failed_rows' => $failed,
            'errors' => array_slice($errors, 0, 500),
            'completed_at' => now(),
        ]);
    }
}
