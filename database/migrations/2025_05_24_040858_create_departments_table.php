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
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->bigInteger('pic_id')->nullable();
            $table->bigInteger('manager_id')->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->softDeletesTz();
            $table->bigInteger('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
