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
        Schema::table('work_spaces', function (Blueprint $table) {
            $table->uuid('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_spaces', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');

            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
