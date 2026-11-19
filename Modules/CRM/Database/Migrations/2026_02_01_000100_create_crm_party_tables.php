<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_accounts', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->unsignedInteger('employee_count')->nullable();
            $table->money('annual_revenue', true);
            $table->text('tax_number')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('crm_accounts')->nullOnDelete();
            $table->unsignedBigInteger('source_lead_id')->nullable()->index();
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'owner_id']);
            $table->fullText(['name', 'email']);
        });

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->foreignId('crm_account_id')->nullable()->constrained('crm_accounts')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->json('address')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('source_lead_id')->nullable()->index();
            $table->date('date_of_birth')->nullable();
            $table->boolean('do_not_email')->default(false);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'crm_account_id']);
            $table->index(['company_id', 'email']);
            $table->fullText(['first_name', 'last_name', 'email']);
        });

        Schema::create('lead_sources', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('lead_statuses', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->string('color', 20)->default('slate');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_converted')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->branchScoped();
            $table->string('number', 30);
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->foreignId('lead_source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->foreignId('lead_status_id')->nullable()->constrained('lead_statuses')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('score')->default(0);
            $table->money('estimated_value', true);
            $table->string('currency_code', 3)->nullable();
            $table->json('address')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->unsignedBigInteger('converted_contact_id')->nullable();
            $table->unsignedBigInteger('converted_account_id')->nullable();
            $table->unsignedBigInteger('converted_deal_id')->nullable();
            $table->string('lost_reason')->nullable();
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'lead_status_id']);
            $table->index(['company_id', 'owner_id']);
            $table->index(['company_id', 'email']);
        });

        Schema::create('lead_scoring_rules', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('field', 50);
            $table->string('operator', 20);
            $table->string('value')->nullable();
            $table->smallInteger('points');
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_scoring_rules');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_statuses');
        Schema::dropIfExists('lead_sources');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('crm_accounts');
    }
};
