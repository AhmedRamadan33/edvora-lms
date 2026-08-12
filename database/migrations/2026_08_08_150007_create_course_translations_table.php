<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->unique(['course_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_translations');
    }
};
