<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SMART NOTIFICATION HUB
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['mandatory', 'info'])->default('info'); // tab "Wajib Dikerjakan" vs "Informasi Lainnya"
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable(); // URL ke halaman terkait
            $table->boolean('read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'read', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
