<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perangkat_id')->constrained('perangkat')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('foto')->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal');
            $table->time('waktu');
            $table->enum('status_perangkat', ['bagus', 'rusak']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasional');
    }
};
