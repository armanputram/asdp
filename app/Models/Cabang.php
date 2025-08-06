<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Cabang extends Model
{
    use HasFactory;

    protected $table = 'cabang';

    protected $fillable = [
        'nama',
    ];

    // Relasi ke Pelabuhan
    public function pelabuhan()
    {
        return $this->hasMany(Pelabuhan::class);
    }
}
