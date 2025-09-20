<?php

namespace App\Models\Rencana_Biaya;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;
use App\Models\PKAT\TransRkia;

class TransRencanaBiaya extends Model
{
    protected $table = 'trans_rencana_biaya';
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

    //rkia_id
    public function rkia()
    {
        return $this->belongsTo(TransRkia::class, 'rkia_id', 'id');
    }
}
