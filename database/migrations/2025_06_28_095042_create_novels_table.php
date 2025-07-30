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
        Schema::create('novels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('unique_name')->unique();
            $table->text('description');
            $table->text('synopsis');
            $table->text('tags');
            $table->text('image')->nullable();
            $table->string('image_public_id')->nullable();
            $table->enum('status', config('base.status'))->default('draft');
            $table->enum('progress', config('base.progress'))->default('ongoing');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novels');
    }
};
