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

    // self-join: parent organisasi
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    // children organisasi
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    // perusahaan induk (menunjuk ke org_struct lain)
    public function perusahaan()
    {
        return $this->belongsTo(self::class, 'perusahaan_id', 'id');
    }

    // kota lokasi perusahaan/provider
    public function city()
    {
        return $this->belongsTo(RefCity::class, 'city_id', 'id');
    }

    // user yang perusahaan_id-nya menunjuk ke org_struct ini
    public function usersByPerusahaan()
    {
        return $this->hasMany(SysUser::class, 'perusahaan_id', 'id');
    }

    // user yang provider_id-nya menunjuk ke org_struct ini
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
