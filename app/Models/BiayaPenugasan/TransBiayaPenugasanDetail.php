<?php

namespace App\Models\Pelaporan;

use Illuminate\Database\Eloquent\Model;

class TransBiayaPenugasanDetail extends Model
{
    protected $table = 'trans_biaya_penugasan_details';
    protected $guarded = [];
    public $timestamps = true;

    public function biaya() { return $this->belongsTo(TransBiayaPenugasan::class, 'biaya_id'); }
}
