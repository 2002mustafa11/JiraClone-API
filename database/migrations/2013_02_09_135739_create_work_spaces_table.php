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
        Schema::create('work_spaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');

            // $table->uuid('manager_id');
            // $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');

            // $table->uuid('company_id');
            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_spaces');
    }
};
