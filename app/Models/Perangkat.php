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
        'nama',
        'qty',
    ];

        public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id'); // atau 'layanan_id'
    }

    }
