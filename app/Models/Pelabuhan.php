<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\JenisLayanan;


class Pelabuhan extends Model
{
    use HasFactory;

    protected $table = 'pelabuhan';

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
         public function perangkats()
    {
        return $this->hasMany(Perangkat::class);
    }
    public function layanans()
{
    return $this->hasMany(Layanan::class);
}
}
