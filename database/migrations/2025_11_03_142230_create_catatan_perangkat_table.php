<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_perangkat', function (Blueprint $table) {
            $table->id();

            // Foreign keys dengan nama tabel yang benar (tanpa 's')
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->foreignId('pelabuhan_id')->constrained('pelabuhan')->onDelete('cascade');
            $table->foreignId('layanan_id')->constrained('layanan')->onDelete('cascade');
            $table->foreignId('perangkat_id')->constrained('perangkat')->onDelete('cascade');

            $table->integer('qty_check'); // Loket 1-10
            $table->text('catatan');
            $table->boolean('is_selesai')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index untuk query yang lebih cepat
            $table->index(['cabang_id', 'pelabuhan_id', 'layanan_id', 'perangkat_id', 'qty_check', 'is_selesai'], 'idx_catatan_lookup');
        });

        // Update tabel operasional_items untuk menyimpan referensi ke catatan_perangkat
        Schema::table('operasional_items', function (Blueprint $table) {
            $table->foreignId('catatan_perangkat_id')
                ->nullable()
                ->after('catatan')
                ->constrained('catatan_perangkat')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Hapus foreign key dari operasional_items dulu
        Schema::table('operasional_items', function (Blueprint $table) {
            $table->dropForeign(['catatan_perangkat_id']);
            $table->dropColumn('catatan_perangkat_id');
        });

        // Baru hapus tabel catatan_perangkat
        Schema::dropIfExists('catatan_perangkat');
    }
};
