<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefOrgStructs extends Model
{
    protected $table = 'ref_org_structs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $guarded = [];

    // parent organisasi
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    // children organisasi
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    // perusahaan induk
    public function perusahaan()
    {
        return $this->belongsTo(self::class, 'perusahaan_id', 'id');
    }

    // kota lokasi perusahaan/provider
    public function city()
    {
        return $this->belongsTo(RefCity::class, 'city_id', 'id');
    }

    // has many perusahaan/provider dari SysUser
    public function usersByPerusahaan()
    {
        return $this->hasMany(SysUser::class, 'perusahaan_id', 'id');
    }
    public function usersByProvider()
    {
        return $this->hasMany(SysUser::class, 'provider_id', 'id');
    }

    // creator/updater
    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(SysUser::class, 'updated_by', 'id');
    }
}
