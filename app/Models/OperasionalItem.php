<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperasionalItem extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $casts = [
        'tanggal' => 'date',
        'waktu' => 'datetime:H:i',
        'qty' => 'integer',
        'qty_check' => 'integer',
    ];

    protected $dates = [
        'tanggal',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function operasional()
    {
        return $this->belongsTo(Operasional::class);
    }

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class);
    }

    // Scopes
    public function scopeByOperasional($query, $operasionalId)
    {
        return $query->where('operasional_id', $operasionalId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status_perangkat', $status);
    }

    // Accessors
    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return null;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status_perangkat) {
            'baik' => 'success',
            'rusak' => 'danger',
            'maintenance' => 'warning',
            default => 'secondary'
        };
    }
}
