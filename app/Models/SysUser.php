<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class SysUser extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'sys_users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $keyType = 'int';
    public $timestamps = true;


    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'phone',
        'status',
        'type',
        'provider_id',
        'perusahaan_id',
        'position_id',
        'image',
        'npp',
        'nik',
        'jabatan_provider',
        'created_by',
        'updated_by'
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
            'password'          => 'hashed',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
        ];
    }

    public function perusahaan()
    {
        // ref_org_structs.id
        return $this->belongsTo(RefOrgStructs::class, 'perusahaan_id', 'id');
    }

    public function provider()
    {
        // ref_org_structs.id
        return $this->belongsTo(RefOrgStructs::class, 'provider_id', 'id');
    }

    public function position()
    {
        // ref_positions.id
        return $this->belongsTo(RefPositions::class, 'position_id', 'id');
    }

    // Dibuat oleh user siapa
    public function creator()
    {
        return $this->belongsTo(self::class, 'created_by', 'id');
    }

    // Diperbarui oleh user siapa
    public function updater()
    {
        return $this->belongsTo(self::class, 'updated_by', 'id');
    }
}
