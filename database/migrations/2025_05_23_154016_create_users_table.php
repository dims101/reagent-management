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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->nullable();
            $table->bigInteger('dept_id')->nullable();
            $table->string('nup', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('company', 50)->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_default_password')->nullable()->default(true);
            $table->bigInteger('role_id')->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
