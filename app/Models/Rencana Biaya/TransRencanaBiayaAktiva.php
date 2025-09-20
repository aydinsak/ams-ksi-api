<?php

namespace App\Models\Rencana_Biaya;

use App\Models\RefAssetType;
use App\Models\SysUser;
use Illuminate\Database\Eloquent\Model;

class TransRencanaBiayaAktiva extends Model
{
    protected $table = 'trans_rencana_biaya_aktiva';
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

    //jenis_aktiva_id
    public function jenisAktiva()
    {
        return $this->belongsTo(RefAssetType::class, 'jenis_aktiva_id', 'id');
    }
}
