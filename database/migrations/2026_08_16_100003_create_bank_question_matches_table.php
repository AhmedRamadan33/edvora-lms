<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_question_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_question_id')->constrained()->cascadeOnDelete();
            $table->string('prompt_text');
            $table->string('match_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_question_matches');
    }
};
