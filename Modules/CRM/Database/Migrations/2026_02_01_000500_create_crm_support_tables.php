<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->unsignedInteger('first_response_minutes');
            $table->unsignedInteger('resolution_minutes');
            $table->json('business_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'priority', 'is_active']);
        });

        Schema::create('ticket_categories', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('number', 30);
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->foreignId('ticket_category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'pending', 'on_hold', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->nullOnDelete();
            $table->timestamp('first_response_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('sla_breach_notified_at')->nullable();
            $table->enum('channel', ['email', 'portal', 'phone', 'web'])->default('web');
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'status', 'priority']);
            $table->index('assigned_to');
        });

        Schema::create('ticket_replies', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped(false);
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        Schema::create('portal_users', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['company_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_users');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ticket_categories');
        Schema::dropIfExists('sla_policies');
    }
};
