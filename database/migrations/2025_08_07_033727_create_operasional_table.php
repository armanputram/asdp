<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabel utama Operasional (metadata)
        Schema::create('operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // siapa yang input
            $table->foreignId('cabang_id')->constrained('cabang')->cascadeOnDelete();
            $table->foreignId('pelabuhan_id')->constrained('pelabuhan')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->cascadeOnDelete();
            $table->timestamps();
        });

        // Tabel detail item perangkat
            Schema::create('operasional_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('operasional_id')->constrained('operasional')->cascadeOnDelete();
        $table->foreignId('perangkat_id')->constrained('perangkat')->cascadeOnDelete();

        // Jumlah perangkat di database master (data awal)
        $table->integer('qty')->default(0);

        // Jumlah hasil pengecekan lapangan (qty_check)
        $table->integer('qty_check')->default(0);

        $table->string('status_perangkat');
        $table->string('foto')->nullable();
        $table->text('catatan')->nullable();
        $table->date('tanggal');
        $table->time('waktu');

        $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasional_items');
        Schema::dropIfExists('operasional');
    }
};
