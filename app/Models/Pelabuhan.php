<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelabuhan extends Model
{
    use HasFactory;

    protected $table = 'pelabuhan'; // tanpa "s" di belakang

    protected $fillable = [
        'cabang_id',
        'nama',
    ];

    // Relasi ke Cabang
    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    // Relasi ke Jenis Layanan (nanti)
    public function jenisLayanan()
    {
        return $this->hasMany(JenisLayanan::class);
    }
}
