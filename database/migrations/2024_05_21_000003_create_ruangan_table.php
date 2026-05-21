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
        Schema::create('ruangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kampus_id');
            $table->string('nama_ruangan');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('kampus_id')->references('id')->on('kampus')->onDelete('cascade');
            
            // Unique constraint for ruangan per kampus
            $table->unique(['kampus_id', 'nama_ruangan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruangan');
    }
};
