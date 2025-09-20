<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefCostType extends Model
{
    protected $table = 'ref_cost_type';
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
}
