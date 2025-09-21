<?php

namespace App\Models\ProgramAudit;

use Illuminate\Database\Eloquent\Model;

class TransProgramDetail extends Model
{
    protected $table = 'trans_programs_details';
    protected $guarded = [];
    public $timestamps = true;

    public function program() { return $this->belongsTo(TransProgram::class, 'program_id'); }
}
