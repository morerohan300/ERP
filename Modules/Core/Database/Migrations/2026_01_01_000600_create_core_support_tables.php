<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attachments, tags, login history, import/export jobs and backups.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->morphs('attachable');
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('name', 60);
            $table->string('color', 20)->default('slate');
            $table->auditColumns();
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->morphs('taggable');

            $table->primary(['tag_id', 'taggable_type', 'taggable_id']);
        });

        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->boolean('successful');
            $table->string('event', 30)->default('login')->comment('login, logout, failed, two_factor');
            $table->timestamp('occurred_at');

            $table->index(['user_id', 'occurred_at']);
            $table->index('occurred_at');
        });

        Schema::create('data_transfers', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->enum('direction', ['import', 'export']);
            $table->string('module', 40);
            $table->string('entity', 60);
            $table->string('format', 10)->default('xlsx');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->json('filters')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->auditColumns();
            $table->timestamps();

            $table->index(['company_id', 'direction', 'status']);
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('disk', 30)->default('local');
            $table->string('path')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->enum('status', ['queued', 'running', 'completed', 'failed'])->default('queued');
            $table->text('error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('data_transfers');
        Schema::dropIfExists('login_histories');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('attachments');
    }
};
