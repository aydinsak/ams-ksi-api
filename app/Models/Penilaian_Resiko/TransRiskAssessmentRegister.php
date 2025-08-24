<?php

namespace App\Models\Penilaian_Resiko;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;
use App\Models\RefOrgStructs;

class TransRiskAssessmentRegister extends Model
{
    protected $table = 'trans_risk_assessment_register';
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

    // perusahaan/provider (org_struct)
    public function perusahaan()
    {
        return $this->belongsTo(RefOrgStructs::class, 'perusahaan_id', 'id');
    }
    public function provider()
    {
        return $this->belongsTo(RefOrgStructs::class, 'unit_kerja_id', 'id');
    }
}
