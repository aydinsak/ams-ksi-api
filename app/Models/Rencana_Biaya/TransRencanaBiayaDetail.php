<?php

namespace App\Models\Rencana_Biaya;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;
use App\Models\RefCostType;
use App\Models\RefCostComponent;
use App\Models\Rencana_Biaya\TransRencanaBiaya;

class TransRencanaBiayaDetail extends Model
{
    protected $table = 'trans_rencana_biaya_detail';
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

    //jenis_biaya_id
    public function jenisBiaya()
    {
        return $this->belongsTo(RefCostType::class, 'jenis_biaya_id', 'id');
    }

    //komponen_biaya_id
    public function komponenBiaya()
    {
        return $this->belongsTo(RefCostComponent::class, 'komponen_biaya_id', 'id');
    }

    //rencana_biaya_id
    public function rencanaBiaya()
    {
        return $this->belongsTo(TransRencanaBiaya::class, 'rencana_biaya_id', 'id');
    }
}
