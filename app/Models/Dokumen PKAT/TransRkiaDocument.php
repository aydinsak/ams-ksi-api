<?php

namespace App\Models\Dokumen_PKAT;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;
use App\Models\PKAT\TransRkia;

class TransRkiaDocument extends Model
{
    protected $table = 'trans_rkia_document';
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

    //rkia
    public function rkia()
    {
        return $this->belongsTo(TransRkia::class, 'rkia_id', 'id');
    }
}
