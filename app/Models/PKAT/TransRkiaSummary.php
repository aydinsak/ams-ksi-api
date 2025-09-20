<?php

namespace App\Models\PKAT;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;
use App\Models\RefOrgStructs;
use App\Models\Dokumen_PKAT\TransRkia;
use App\Models\RefTypeAudit;
use App\Models\RefRiskRatings;

class TransRkiaSummary extends Model
{
    protected $table = 'trans_rkia_summary';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    protected $guarded = [];

    // SysUser updated_by/created_by/leader_id/pic_id
    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'id');
    }
    public function updater()
    {
        return $this->belongsTo(SysUser::class, 'updated_by', 'id');
    }
    public function pic()
    {
        return $this->belongsTo(SysUser::class, 'pic_id', 'id');
    }
    public function leader()
    {
        return $this->belongsTo(SysUser::class, 'leader_id', 'id');
    }

    //perusahanan (org_struct)
    public function perusahaan()
    {
        return $this->belongsTo(RefOrgStructs::class, 'object_id', 'id');
    }

    //trans_rkia
    public function rkia()
    {
        return $this->belongsTo(TransRkia::class, 'rkia_id', 'id');
    }

    //ref_type_audit
    public function typeAudit()
    {
        return $this->belongsTo(RefTypeAudit::class, 'type_audit_id', 'id');
    }

    //ref_risk_ratings
    public function riskRating()
    {
        return $this->belongsTo(RefRiskRatings::class, 'risk_rating_id', 'id');
    }
}
