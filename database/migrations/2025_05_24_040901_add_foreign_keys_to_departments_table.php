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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign(['pic_id'], 'departments_fk2')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['manager_id'], 'departments_fk3')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign('departments_fk2');
            $table->dropForeign('departments_fk3');
        });
    }
};
