<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CONTEXTUAL MICRO-FORUM
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('forum_posts')->cascadeOnDelete(); // nested replies
            $table->text('body');
            $table->boolean('is_pinned')->default(false);     // pin by lecturer
            $table->boolean('is_lecturer_reply')->default(false);
            $table->timestamps();
            $table->index(['assignment_id', 'parent_id']);
        });

        // UPVOTE SYSTEM (no downvote)
        Schema::create('forum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['forum_post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_votes');
        Schema::dropIfExists('forum_posts');
    }
};
