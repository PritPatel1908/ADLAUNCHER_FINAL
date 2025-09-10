<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unique_id')->unique();
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->string('ip')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0: Delete, 1: Activate, 2: Inactive, 3: Block');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
