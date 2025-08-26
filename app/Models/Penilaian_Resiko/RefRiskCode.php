<?php

namespace App\Models\Penilaian_Resiko;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;

class RefRiskCode extends Model
{
    protected $table = 'ref_risk_code';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    protected $guarded = [];

    // SysUser updated_by/created_by
    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'id');
    }
    public function updater()
    {
        return $this->belongsTo(SysUser::class, 'updated_by', 'id');
    }
}
