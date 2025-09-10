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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_name');
            $table->dateTime('schedule_start_date_time');
            $table->dateTime('schedule_end_date_time');
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('layout_id');
            $table->unsignedBigInteger('screen_id');
            $table->timestamps();

            // FKs
            $table->foreign('device_id')
                ->references('id')->on('devices')
                ->onDelete('cascade');

            $table->foreign('layout_id')
                ->references('id')->on('device_layouts')
                ->onDelete('no action');

            $table->foreign('screen_id')
                ->references('id')->on('device_screens')
                ->onDelete('no action');

            // Indexes
            $table->index(['device_id', 'layout_id', 'screen_id']);
            $table->index(['schedule_start_date_time', 'schedule_end_date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
