<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segments', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('rules')->nullable();
            $table->boolean('is_dynamic')->default(true);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('segment_contact', function (Blueprint $table): void {
            $table->foreignId('segment_id')->constrained('segments')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();

            $table->primary(['segment_id', 'contact_id']);
        });

        Schema::create('campaigns', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->enum('type', ['email', 'sms'])->default('email');
            $table->foreignId('segment_id')->nullable()->constrained('segments')->nullOnDelete();
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->text('sms_body')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->money('budget', true);
            $table->json('stats')->nullable();
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status', 'scheduled_at']);
        });

        Schema::create('campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped(false);
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('segment_contact');
        Schema::dropIfExists('segments');
    }
};
