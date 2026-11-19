<?php

declare(strict_types=1);

namespace Modules\CRM\Exports;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One export for every CRM list: a query, its column headings and a row mapper.
 * Used for synchronous downloads, so the mapper closure never has to be serialized.
 *
 * @implements WithMapping<Model>
 */
class QueryExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param  Builder<Model>  $query
     * @param  array<int, string>  $headings
     * @param  Closure(Model): array<int, mixed>  $mapper
     */
    public function __construct(
        private readonly Builder $query,
        private readonly array $headings,
        private readonly Closure $mapper,
    ) {}

    /** @return Builder<Model> */
    public function query(): Builder
    {
        return $this->query;
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @param  Model  $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        return ($this->mapper)($row);
    }

    /** @return array<int|string, mixed> */
    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
