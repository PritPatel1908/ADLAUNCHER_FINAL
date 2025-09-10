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
        Schema::create('schedule_medias', function (Blueprint $table) {
            $table->id();
            $table->string('media_file');
            $table->unsignedBigInteger('schedule_id');
            $table->string('media_type');
            $table->timestamps();

            $table->foreign('schedule_id')
                ->references('id')->on('schedules')
                ->onDelete('cascade');

            $table->index(['schedule_id']);
            $table->index(['media_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_medias');
    }
};
