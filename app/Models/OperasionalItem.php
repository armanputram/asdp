<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperasionalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'operasional_id',
        'perangkat_id',
        'qty',
        'qty_check',
        'status_perangkat',
        'foto',
        'catatan',
        'tanggal',
        'waktu',
    ];

    public function operasional()
    {
        return $this->belongsTo(Operasional::class);
    }

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class);
    }
}
