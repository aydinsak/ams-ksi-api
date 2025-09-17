<?php

namespace App\Models\Penilaian_Resiko;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;

class TransRiskAssessmentRegisterDetail extends Model
{
    protected $table = 'trans_risk_assessment_register_detail';
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

    // register
    public function register()
    {
        return $this->belongsTo(TransRiskAssessmentRegister::class, 'risk_register_id', 'id');
    }

    // risk type
    public function riskType()
    {
        return $this->belongsTo(RefRiskType::class, 'jenis_resiko_id', 'id');
    }

    // risk code
    public function riskCode()
    {
        return $this->belongsTo(RefRiskType::class, 'kode_resiko_id', 'id');
    }
}
