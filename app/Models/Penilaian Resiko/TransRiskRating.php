<?php

namespace App\Models\Penilaian_Resiko;

use App\Models\RefRiskRatings;
use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;

class TransRiskRating extends Model
{
    protected $table = 'trans_risk_rating';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    protected $guarded = [];

    //created_by/updated_by
    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'id');
    }
    public function updater()
    {
        return $this->belongsTo(SysUser::class, 'updated_by', 'id');
    }

    //risk_assessment_register_id
    public function riskAssessment()
    {
        return $this->belongsTo(TransRiskAssessmentRegister::class, 'risk_assessment_id', 'id');
    }

    //risk_rating_id
    public function riskRating()
    {
        return $this->belongsTo(RefRiskRatings::class, 'risk_rating_id', 'id');
    }
}
