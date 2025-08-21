<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefPositions extends Model
{
    protected $table = 'ref_positions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $guarded = [];

    // user yang memegang posisi ini
    public function users()
    {
        return $this->hasMany(SysUser::class, 'position_id', 'id');
    }

    // creator/updater (kalau dipakai)
    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(SysUser::class, 'updated_by', 'id');
    }
}
