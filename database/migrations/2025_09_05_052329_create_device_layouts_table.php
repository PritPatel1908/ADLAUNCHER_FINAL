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
        Schema::create('device_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('layout_name');
            $table->tinyInteger('layout_type')->comment('0=full_screen, 1=split_screen, 2=three_grid_screen, 3=four_grid_screen');
            $table->unsignedBigInteger('device_id');
            $table->tinyInteger('status')->default(1)->comment('0=delete, 1=active, 2=inactive, 3=block');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');

            // Indexes
            $table->index('device_id');
            $table->index('layout_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_layouts');
    }
};
