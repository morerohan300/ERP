<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Key/value settings, per-company module toggles and custom fields.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete()->comment('null = global');
            $table->string('group', 40);
            $table->string('key', 80);
            $table->longText('value')->nullable()->comment('JSON, encrypted when is_encrypted');
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'group', 'key']);
        });

        Schema::create('company_modules', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('module', 40);
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->primary(['company_id', 'module']);
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->companyScoped();
            $table->string('entity_type', 60);
            $table->string('name', 60);
            $table->string('label', 120);
            $table->enum('type', ['text', 'textarea', 'number', 'date', 'select', 'multiselect', 'checkbox', 'email', 'url']);
            $table->json('options')->nullable();
            $table->string('default_value')->nullable();
            $table->string('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('show_in_table')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'entity_type', 'name']);
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained('custom_fields')->cascadeOnDelete();
            $table->morphs('model');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['custom_field_id', 'model_type', 'model_id'], 'custom_field_values_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('company_modules');
        Schema::dropIfExists('settings');
    }
};
