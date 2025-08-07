<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operasional extends Model
{
    use HasFactory;
    protected $table = 'operasional';
    protected $fillable = [
        'perangkat_id',
        'user_id',
        'status_foto_required',
        'foto',
        'catatan',
        'tanggal',
        'waktu',
        'status_perangkat',
    ];

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
