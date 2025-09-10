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
        Schema::create('device_screens', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('screen_no');
            $table->unsignedInteger('screen_height');
            $table->unsignedInteger('screen_width');
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('layout_id');
            $table->timestamps();

            // FKs
            $table->foreign('device_id')
                ->references('id')->on('devices')
                ->onDelete('cascade'); // keep cascade here

            $table->foreign('layout_id')
                ->references('id')->on('device_layouts')
                ->onDelete('no action'); // or ->restrictOnDelete()

            // Indexes
            $table->index(['device_id', 'layout_id']);
            $table->index('screen_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_screens');
    }
};
