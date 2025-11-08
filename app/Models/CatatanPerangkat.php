<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatatanPerangkat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catatan_perangkat';

    protected $fillable = [
        'cabang_id',
        'pelabuhan_id',
        'layanan_id',
        'perangkat_id',
        'qty_check',
        'catatan',
        'is_selesai',
        'created_by',
        'tanggal_selesai',
    ];

    protected $casts = [
        'is_selesai' => 'boolean',
        'tanggal_selesai' => 'datetime',
        'qty_check' => 'integer',
    ];

    // Relationships
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

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function operasionalItems()
    {
        return $this->hasMany(OperasionalItem::class, 'catatan_perangkat_id');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('is_selesai', false);
    }

    public function scopeSelesai($query)
    {
        return $query->where('is_selesai', true);
    }

    public function scopeByPerangkat($query, $cabangId, $pelabuhanId, $layananId, $perangkatId, $qtyCheck)
    {
        return $query->where('cabang_id', $cabangId)
                    ->where('pelabuhan_id', $pelabuhanId)
                    ->where('layanan_id', $layananId)
                    ->where('perangkat_id', $perangkatId)
                    ->where('qty_check', $qtyCheck);
    }

    // Methods
    public function markAsSelesai()
    {
        $this->update([
            'is_selesai' => true,
            'tanggal_selesai' => now(),
        ]);
    }

    public static function getCatatanAktif($cabangId, $pelabuhanId, $layananId, $perangkatId, $qtyCheck)
    {
        return self::byPerangkat($cabangId, $pelabuhanId, $layananId, $perangkatId, $qtyCheck)
                    ->aktif()
                    ->latest()
                    ->first();
    }
}
