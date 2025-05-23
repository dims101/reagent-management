<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->bigInteger('request_no')->primary();
            $table->bigInteger('reagent_id')->nullable();
            $table->decimal('request_qty', 10)->nullable();
            $table->string('purpose', 200)->nullable();
            $table->bigInteger('requested_by')->nullable();
            $table->bigInteger('approval_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
