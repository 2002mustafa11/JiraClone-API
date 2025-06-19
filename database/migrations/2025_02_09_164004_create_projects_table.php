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
        Schema::create('projects', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description');

            $table->uuid('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');

            $table->uuid('workspace_id')->nullable();
            $table->foreign('workspace_id')->references('id')->on('work_spaces')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
