<?php

namespace App\Models\ProgramAudit;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransAssignment;
use App\Models\PKAT\TransRkiaSummary;

class TransProgram extends Model
{
    protected $table = 'trans_programs';
    protected $guarded = [];
    public $timestamps = true;

    public function assignment() { return $this->belongsTo(TransAssignment::class, 'assignment_id'); }
    public function summary()    { return $this->belongsTo(TransRkiaSummary::class, 'summary_id'); }
    public function details()    { return $this->hasMany(TransProgramDetail::class, 'program_id'); }
}
