<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenants (companies), their branches and departments, company
     * membership, and the ERP-specific user columns.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->text('tax_number')->nullable()->comment('encrypted');
            $table->string('registration_number', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('website')->nullable();
            $table->json('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->char('base_currency_code', 3);
            $table->string('locale', 10)->default('en');
            $table->string('timezone', 64)->default('UTC');
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(1);
            $table->string('date_format', 20)->default('Y-m-d');
            $table->string('industry', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('base_currency_code')->references('code')->on('currencies');
            $table->index('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('locale', 10)->nullable()->after('avatar_path');
            $table->string('timezone', 64)->nullable()->after('locale');
            $table->foreignId('current_company_id')->nullable()->after('timezone')->constrained('companies')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('current_company_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->string('code', 20);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->json('address')->nullable();
            $table->boolean('is_head_office')->default(false);
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->branchScoped();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'name']);
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('branch_ids')->nullable()->comment('null = access to all branches');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('job_title')->nullable();
            $table->boolean('is_owner')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->primary(['company_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_company_id');
            $table->dropColumn(['uuid', 'phone', 'avatar_path', 'locale', 'timezone', 'is_active', 'last_login_at', 'deleted_at']);
        });

        Schema::dropIfExists('companies');
    }
};
