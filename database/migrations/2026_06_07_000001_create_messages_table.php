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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignId('sent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receive_id')->constrained('users')->onDelete('cascade');
            $table->longText('message');
            $table->boolean('status_seen')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes untuk performa query
            $table->index(['sent_id', 'receive_id']);
            $table->index(['receive_id', 'status_seen']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
