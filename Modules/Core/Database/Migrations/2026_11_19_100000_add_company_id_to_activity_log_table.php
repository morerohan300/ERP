<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scope audit-log entries to a company so each tenant only sees its own.
     */
    public function up(): void
    {
        Schema::table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->cascadeOnDelete();
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
