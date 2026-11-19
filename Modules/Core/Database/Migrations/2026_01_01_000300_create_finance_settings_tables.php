<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Currencies enabled per company, exchange rates, taxes, fiscal years
     * and document number sequences.
     */
    public function up(): void
    {
        Schema::create('company_currencies', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->char('currency_code', 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->primary(['company_id', 'currency_code']);
            $table->foreign('currency_code')->references('code')->on('currencies');
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->char('from_currency', 3);
            $table->char('to_currency', 3);
            $table->exchangeRate('rate');
            $table->date('effective_date');
            $table->string('source', 30)->default('manual');
            $table->auditColumns();
            $table->timestamps();

            $table->foreign('from_currency')->references('code')->on('currencies');
            $table->foreign('to_currency')->references('code')->on('currencies');
            $table->unique(['company_id', 'from_currency', 'to_currency', 'effective_date'], 'exchange_rates_pair_date_unique');
        });

        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('name', 80);
            $table->string('code', 30);
            $table->decimal('rate', 7, 4);
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->enum('scope', ['sales', 'purchase', 'both'])->default('both');
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_inclusive')->default(false);
            $table->unsignedBigInteger('sales_account_id')->nullable()->comment('FK added by Accounting module');
            $table->unsignedBigInteger('purchase_account_id')->nullable()->comment('FK added by Accounting module');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('tax_groups', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('name', 80);
            $table->string('code', 30);
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('tax_group_tax', function (Blueprint $table) {
            $table->foreignId('tax_group_id')->constrained('tax_groups')->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained('taxes')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence')->default(1);

            $table->primary(['tax_group_id', 'tax_id']);
        });

        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('name', 40);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->auditColumns();
            $table->timestamps();

            $table->unique(['company_id', 'start_date']);
            $table->index(['company_id', 'start_date', 'end_date']);
        });

        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->string('prefix', 20);
            $table->string('format', 60);
            $table->unsignedInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding')->default(4);
            $table->enum('reset_period', ['never', 'yearly', 'monthly'])->default('yearly');
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
        Schema::dropIfExists('fiscal_years');
        Schema::dropIfExists('tax_group_tax');
        Schema::dropIfExists('tax_groups');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('company_currencies');
    }
};
