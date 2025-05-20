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
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('spk_no', 20)->nullable();
            $table->string('request_qty', 50)->nullable();
            $table->bigInteger('requested_by')->nullable();
            $table->string('reagent_name', 50)->nullable();
            $table->string('purpose', 200)->nullable();
            $table->timestampTz('expected_date')->nullable();
            $table->string('status', 20)->nullable();
            $table->timestampTz('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
