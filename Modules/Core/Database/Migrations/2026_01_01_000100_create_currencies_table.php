<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global ISO-4217 currency list (shared by all companies).
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->char('code', 3)->primary();
            $table->string('name', 80);
            $table->string('symbol', 10);
            $table->unsignedTinyInteger('decimals')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
