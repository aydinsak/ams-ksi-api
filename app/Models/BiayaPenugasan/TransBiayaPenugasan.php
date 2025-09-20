<?php

namespace App\Models\Pelaporan;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransAssignment;
use App\Models\PKAT\TransRkiaSummary;

class TransBiayaPenugasan extends Model
{
    protected $table = 'trans_biaya_penugasan';
    protected $guarded = [];
    public $timestamps = true;

    public function assignment() { return $this->belongsTo(TransAssignment::class, 'assignment_id'); }
    public function summary()    { return $this->belongsTo(TransRkiaSummary::class, 'summary_id'); }
    public function details()    { return $this->hasMany(TransBiayaPenugasanDetail::class, 'biaya_id'); }

    // ringkasan total
    public function getTotalAttribute()
    {
        return $this->relationLoaded('details')
            ? $this->details->sum('subtotal')
            : 0;
    }
}
