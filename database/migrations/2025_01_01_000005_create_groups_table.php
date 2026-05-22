<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // KELOMPOK
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // ANGGOTA KELOMPOK (dengan role leader/member)
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        // SUB-TASK (untuk Task-Based Progress Tracker — bukan self-report!)
        Schema::create('group_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->string('proof_file')->nullable(); // bukti file (wajib untuk mark as done)
            $table->dateTime('completed_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // SHARED SANDBOX (file upload kelompok)
        Schema::create('group_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // NUDGE TRACKING (untuk One-Click Gentle Nudge + Escalate to Lecturer)
        Schema::create('nudges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['gentle', 'escalation'])->default('gentle');
            $table->boolean('seen')->default(false);
            $table->timestamps();
            $table->index(['group_id', 'to_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nudges');
        Schema::dropIfExists('group_files');
        Schema::dropIfExists('group_tasks');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
};
