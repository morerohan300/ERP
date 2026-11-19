<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Configurable multi-level approval workflows per document type.
     */
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('document_type', 60);
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions')->nullable()->comment('[{field, operator, value}] — all must match');
            $table->unsignedSmallInteger('priority')->default(0)->comment('lower runs first when several match');
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'document_type', 'is_active']);
        });

        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order');
            $table->string('name');
            $table->enum('approver_type', ['user', 'role', 'department_manager']);
            $table->unsignedBigInteger('approver_id')->nullable()->comment('user id or role id');
            $table->unsignedTinyInteger('min_approvals')->default(1);
            $table->timestamps();

            $table->unique(['approval_workflow_id', 'step_order']);
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('number', 30);
            $table->foreignId('approval_workflow_id')->constrained('approval_workflows');
            $table->morphs('approvable');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->auditColumns();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->foreignId('approval_step_id')->constrained('approval_steps')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('action', ['approved', 'rejected', 'commented']);
            $table->text('comment')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index(['approval_request_id', 'approval_step_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_workflows');
    }
};
