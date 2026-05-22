<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TUGAS
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions');
            $table->dateTime('due_at');
            $table->enum('type', ['individual', 'group'])->default('individual');
            $table->integer('max_score')->default(100);
            $table->timestamps();
        });

        // SUBMISSION MURID
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['draft', 'submitted', 'graded', 'late'])->default('draft');
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->float('ai_similarity_score')->nullable(); // untuk AI Assist
            $table->timestamps();
            $table->unique(['assignment_id', 'user_id']);
        });

        // AUTO-VAULT (materi kuliah)
        Schema::create('vault_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('week')->nullable();          // hasil auto-categorization
            $table->string('topic')->nullable();          // hasil auto-categorization
            $table->boolean('ai_categorized')->default(false); // flag untuk Manual Override
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_files');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
    }
};
