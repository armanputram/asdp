<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    protected $fillable = [
        'cabang_id',
        'pelabuhan_id',
        'nama',
    ];


    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }


    public function pelabuhan()
    {
        return $this->belongsTo(Pelabuhan::class);
    }

    public function perangkat()
    {
        return $this->hasMany(Perangkat::class, 'layanan_id');
    }
}
