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
        Schema::table('role_assignments', function (Blueprint $table) {
            $table->foreign(['user_id'], 'role_assignments_fk1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['role_id'], 'role_assignments_fk2')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_assignments', function (Blueprint $table) {
            $table->dropForeign('role_assignments_fk1');
            $table->dropForeign('role_assignments_fk2');
        });
    }
};
