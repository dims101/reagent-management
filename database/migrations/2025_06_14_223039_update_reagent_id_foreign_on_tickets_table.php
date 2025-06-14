<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the old foreign key
            $table->dropForeign(['reagent_id']);
            // Add the new foreign key to reagents table
            $table->foreign('reagent_id')->references('id')->on('reagents')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['reagent_id']);
            // Restore the old foreign key to stocks table
            $table->foreign('reagent_id')->references('id')->on('stocks')->onDelete('set null');
        });
    }
};
