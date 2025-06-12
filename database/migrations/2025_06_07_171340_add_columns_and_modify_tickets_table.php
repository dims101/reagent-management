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
        Schema::table('tickets', function (Blueprint $table) {
            // Add new columns
            $table->timestampTz('start_date')->nullable()->after('expected_date');
            $table->timestampTz('end_date')->nullable()->after('start_date');
            $table->string('attachment', 255)->nullable()->after('end_date');
            $table->string('uom', 20)->nullable()->after('attachment');
            $table->string('reject_reason', 255)->nullable()->after('uom');
            $table->string('expected_reason', 255)->nullable()->after('reject_reason');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('expected_reason'); // changed to unsignedBigInteger

            // Add reagent_id column
            $table->unsignedBigInteger('reagent_id')->nullable()->after('requested_by');

            // Add foreign key constraints
            $table->foreign('reagent_id')->references('id')->on('stocks')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        // Remove the reagent_name column after adding reagent_id
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('reagent_name');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['reagent_id']);
            $table->dropForeign(['assigned_to']);

            // Drop new columns
            $table->dropColumn(['start_date', 'end_date', 'attachment', 'reagent_id', 'uom', 'reject_reason', 'expected_reason', 'assigned_to']);

            // Re-add reagent_name column
            $table->string('reagent_name', 50)->nullable()->after('requested_by');
        });
    }
};
