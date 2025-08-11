<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operasional extends Model
{
    use HasFactory;

    protected $table = 'operasional';

    protected $fillable = [
        'user_id',
        'cabang_id',
        'pelabuhan_id',
        'layanan_id',
    ];

    public function items()
    {
        return $this->hasMany(OperasionalItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function pelabuhan()
    {
        return $this->belongsTo(Pelabuhan::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

}
