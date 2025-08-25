<?php

namespace App\Models\Dokumen_PKAT;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;
use App\Models\RefOrgStructs;

class TransRkia extends Model
{
    protected $table = 'trans_rkia_document';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    protected $guarded = [];

    // SysUser updated_by/created_by/pic_id
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

    //perusahanan (org_struct)
    public function perusahaan()
    {
        return $this->belongsTo(RefOrgStructs::class, 'perusahaan_id', 'id');
    }
}
