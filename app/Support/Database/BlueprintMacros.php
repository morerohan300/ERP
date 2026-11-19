<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

/**
 * Schema helpers shared by every module's migrations so the standard ERP
 * columns are identical everywhere:
 *
 *   $table->id();
 *   $table->companyScoped();        // uuid + company_id FK (indexed)
 *   ... domain columns ...
 *   $table->auditColumns();         // created_by / updated_by FKs
 *   $table->timestamps();
 *   $table->softDeletes();
 */
final class BlueprintMacros
{
    public static function register(): void
    {
        Blueprint::macro('companyScoped', function (bool $withUuid = true): void {
            /** @var Blueprint $this */
            if ($withUuid) {
                $this->uuid('uuid')->unique();
            }
            $this->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
        });

        Blueprint::macro('branchScoped', function (): void {
            /** @var Blueprint $this */
            $this->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
        });

        Blueprint::macro('auditColumns', function (): void {
            /** @var Blueprint $this */
            $this->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Blueprint::macro('money', function (string $column, bool $nullable = false): ColumnDefinition {
            /** @var Blueprint $this */
            $definition = $this->decimal($column, 18, 4);

            return $nullable ? $definition->nullable() : $definition->default(0);
        });

        Blueprint::macro('quantity', function (string $column): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->decimal($column, 18, 4)->default(0);
        });

        Blueprint::macro('exchangeRate', function (string $column = 'exchange_rate'): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->decimal($column, 18, 8)->default(1);
        });
    }
}
