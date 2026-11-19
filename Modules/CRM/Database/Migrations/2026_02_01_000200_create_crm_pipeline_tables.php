<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('probability')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->string('color', 20)->default('slate');
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_id', 'sort_order']);
        });

        Schema::create('deals', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped();
            $table->branchScoped();
            $table->string('number', 30);
            $table->foreignId('pipeline_id')->constrained('pipelines');
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages');
            $table->string('title');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('crm_account_id')->nullable()->constrained('crm_accounts')->nullOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->money('value');
            $table->string('currency_code', 3)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->unsignedTinyInteger('probability')->default(0);
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->unsignedBigInteger('sales_order_id')->nullable()->index();
            $table->unsignedInteger('kanban_order')->default(0);
            $table->text('description')->nullable();
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'pipeline_id', 'pipeline_stage_id', 'kanban_order'], 'deals_kanban_index');
            $table->index(['company_id', 'owner_id']);
        });

        Schema::create('deal_products', function (Blueprint $table): void {
            $table->id();
            $table->companyScoped(false);
            $table->foreignId('deal_id')->constrained('deals')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('description');
            $table->quantity('quantity');
            $table->money('unit_price');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->money('tax_amount');
            $table->money('line_total');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_products');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
    }
};
