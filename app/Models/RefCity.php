<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefCity extends Model
{
    protected $table = 'ref_city';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $guarded = [];

    public function province()
    {
        return $this->belongsTo(RefProvince::class, 'province_id', 'id');
    }
}
