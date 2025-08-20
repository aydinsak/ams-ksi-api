<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefProvince extends Model
{
    protected $table = 'ref_province';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $guarded = [];

    //FK city
    public function cities()
    {
        return $this->hasMany(RefCity::class, 'province_id', 'id');
    }
}
