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
        Schema::create('stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reagent_name', 100)->nullable();
            $table->string('po_no', 20)->nullable();
            $table->string('maker', 50)->nullable();
            $table->string('catalog_no', 20)->nullable();
            $table->string('site', 50)->nullable();
            $table->string('price', 12)->nullable();
            $table->bigInteger('lead_time')->nullable();
            $table->string('initial_qty', 20)->nullable();
            $table->string('remaining_qty', 20)->nullable();
            $table->string('minimum_qty', 20)->nullable();
            $table->string('quantity_uom', 10)->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->date('expired_date')->nullable();
            $table->bigInteger('dept_owner_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
