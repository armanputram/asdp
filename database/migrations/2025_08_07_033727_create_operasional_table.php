<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel utama Operasional (metadata)
        Schema::create('operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabang_id')->constrained('cabang')->cascadeOnDelete();  
            $table->foreignId('pelabuhan_id')->constrained('pelabuhan')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->cascadeOnDelete();

            // Kolom validasi tanda tangan
            $table->boolean('is_validated')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });

        // Tabel detail item perangkat
        Schema::create('operasional_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operasional_id')->constrained('operasional')->cascadeOnDelete();
            $table->foreignId('perangkat_id')->constrained('perangkat')->cascadeOnDelete();
            $table->integer('qty')->nullable();
            $table->string('qty_check')->default('1');
            $table->string('status_perangkat');
            $table->string('foto')->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal');
            $table->time('waktu');
            $table->softDeletes();
            $table->timestamps();
        });

        // Kolom tanda tangan di tabel users (hanya jika belum ada)
        if (!Schema::hasColumn('users', 'signature')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('signature')->nullable()->after('password');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('operasional_items');
        Schema::dropIfExists('operasional');

        if (Schema::hasColumn('users', 'signature')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('signature');
            });
        }
    }
};
