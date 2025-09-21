<?php

namespace App\Models\Dokumen_PKAT;

use Illuminate\Database\Eloquent\Model;
use App\Models\SysUser;
use App\Models\RefOrgStructs;

class TransRkia extends Model
{
    protected $table = 'trans_rkia_document_cc';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'document_id',
        'user_id',
    ];

    protected $casts = [
        'document_id' => 'int',
        'user_id'     => 'int',
    ];

    public function document()
    {
        return $this->belongsTo(TransRkiaDocument::class, 'document_id');
    }
    public function user()
    {
        return $this->belongsTo(SysUser::class, 'user_id');
    }
}
