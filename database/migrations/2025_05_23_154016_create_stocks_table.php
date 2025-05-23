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
            $table->decimal('price', 10)->nullable();
            $table->decimal('lead_time', 10, 0)->nullable();
            $table->decimal('initial_qty', 10)->nullable();
            $table->decimal('remaining_qty', 10)->nullable();
            $table->decimal('minimum_qty', 10)->nullable();
            $table->string('quantity_uom', 10)->nullable();
            $table->date('expired_date')->nullable();
            $table->bigInteger('dept_owner_id')->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->softDeletesTz();
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
