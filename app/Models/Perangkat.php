<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Perangkat extends Model
{
    use HasFactory;

    protected $table = 'perangkat';

    protected $fillable = [
        'layanan_id',
        'cabang_id',
        'pelabuhan_id',
        'nama',
        'qty',
        'keterangan',
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function pelabuhan()
    {
        return $this->belongsTo(Pelabuhan::class, 'pelabuhan_id');
    }

    protected static function booted()
    {
        static::creating(function ($perangkat) {
            if ($perangkat->layanan_id) {
                $layanan = Layanan::find($perangkat->layanan_id);
                if ($layanan) {
                    $perangkat->cabang_id = $layanan->cabang_id;
                    $perangkat->pelabuhan_id = $layanan->pelabuhan_id;
                }
            }
        });
    }
}
