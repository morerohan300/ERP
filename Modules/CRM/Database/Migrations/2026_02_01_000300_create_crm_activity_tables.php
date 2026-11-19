<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->enum('type', ['call', 'meeting', 'task', 'note', 'email'])->default('task');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->nullableMorphs('related', 'activities_related_index');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->string('outcome')->nullable();
            $table->string('priority', 10)->default('normal');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'owner_id', 'due_at']);
            $table->index(['company_id', 'starts_at']);
            $table->index(['reminder_at', 'reminder_sent_at']);
        });

        Schema::create('activity_participants', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped(false);
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->morphs('participant');
            $table->timestamps();

            $table->unique(['activity_id', 'participant_type', 'participant_id'], 'activity_participant_unique');
        });

        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->string('module', 30)->default('crm');
            $table->string('subject');
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('email_logs', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->nullableMorphs('emailable');
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->string('from_address')->nullable();
            $table->string('to_address');
            $table->string('cc_address')->nullable();
            $table->string('subject');
            $table->longText('body');
            $table->string('status', 20)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->text('error')->nullable();
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('activity_participants');
        Schema::dropIfExists('activities');
    }
};
