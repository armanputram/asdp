<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'signature',
        'staff_it_id', // Tambahkan ini
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke Operasional
     */
    public function operasional()
    {
        return $this->hasMany(Operasional::class);
    }

    /**
     * Relasi ke Staff IT (atasan)
     * User ini memiliki seorang Staff IT sebagai atasan
     */
    public function staffIt()
    {
        return $this->belongsTo(User::class, 'staff_it_id');
    }

    /**
     * Relasi ke Petugas TI (bawahan)
     * User ini memiliki banyak Petugas TI sebagai bawahan
     */
    public function petugasTi()
    {
        return $this->hasMany(User::class, 'staff_it_id');
    }
}
