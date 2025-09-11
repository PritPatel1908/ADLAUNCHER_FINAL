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
        Schema::create('data_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name');
            $table->boolean('self_only')->default(false);
            $table->boolean('allow_all_location')->default(false);
            $table->boolean('allow_all_company')->default(false);
            $table->boolean('allow_all_area')->default(false);
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
        Schema::dropIfExists('data_policies');
    }
};
