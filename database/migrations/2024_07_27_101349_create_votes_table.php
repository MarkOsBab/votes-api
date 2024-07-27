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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_id');
            $table->unsignedBigInteger('candidate_voted_id');
            $table->date('date');

            $table->foreign('candidate_id')
                  ->references('id')
                  ->on('voters')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('candidate_voted_id')
                  ->references('id')
                  ->on('voters')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
