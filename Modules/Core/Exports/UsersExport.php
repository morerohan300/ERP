<?php

declare(strict_types=1);

namespace Modules\Core\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @implements WithMapping<User>
 */
class UsersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /** @param Builder<User> $query */
    public function __construct(private readonly Builder $query) {}

    /** @return Builder<User> */
    public function query(): Builder
    {
        return $this->query->with('roles');
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [__('Name'), __('Email'), __('Phone'), __('Roles'), __('Status'), __('Two-factor'), __('Last login'), __('Created')];
    }

    /**
     * @param  User  $user
     * @return array<int, mixed>
     */
    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->phone,
            $user->roles->pluck('name')->implode(', '),
            $user->is_active ? __('Active') : __('Inactive'),
            $user->hasTwoFactorEnabled() ? __('Enabled') : __('Disabled'),
            $user->last_login_at?->toDateTimeString(),
            $user->created_at?->toDateString(),
        ];
    }

    /** @return array<int|string, mixed> */
    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
