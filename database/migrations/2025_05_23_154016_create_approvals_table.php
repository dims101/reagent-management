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
        Schema::create('approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reason', 200)->nullable();
            $table->bigInteger('dept_id')->nullable();
            $table->bigInteger('assigned_pic_id')->nullable();
            $table->date('assigned_pic_date')->nullable();
            $table->bigInteger('assigned_manager_id')->nullable();
            $table->date('assigned_manager_date')->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
