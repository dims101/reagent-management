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
        Schema::table('requests', function (Blueprint $table) {
            $table->foreign(['reagent_id'], 'requests_fk1')->references(['id'])->on('stocks')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['requested_by'], 'requests_fk4')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['approval_id'], 'requests_fk5')->references(['id'])->on('approvals')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign('requests_fk1');
            $table->dropForeign('requests_fk4');
            $table->dropForeign('requests_fk5');
        });
    }
};
