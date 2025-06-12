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
        Schema::table('approvals', function (Blueprint $table) {
            $table->text('approval_reason')->nullable()->after('reason');
            // Rename existing 'reason' to 'reject_reason' for clarity
            $table->renameColumn('reason', 'reject_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropColumn('approval_reason');
            $table->renameColumn('reject_reason', 'reason');
        });
    }
};
