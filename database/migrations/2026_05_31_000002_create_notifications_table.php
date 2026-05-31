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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
            $table->string('judul');
            $table->text('pesan');
            $table->string('keterangan')->nullable();
            $table->enum('jenis', ['booking_success', 'kelas_reminder', 'admin_manual']);
            $table->enum('sumber', ['system', 'admin']);
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamp('dikirim_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dibaca_pada']);
            $table->index(['jenis', 'sumber']);
            $table->index(['booking_id', 'jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
