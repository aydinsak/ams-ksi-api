<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransAssignment extends Model
{
    protected $table = 'trans_assignments';
    protected $guarded = [];
    protected $casts = [
        'letter_date' => 'datetime',
        'date_start'  => 'datetime',
        'date_end'    => 'datetime',
    ];

    public function summary()
    {
        return $this->belongsTo(\App\Models\PKAT\TransRkiaSummary::class, 'summary_id');
    }

    public function leader()
    {
        return $this->belongsTo(\App\Models\SysUser::class, 'leader_id');
    }

    public function pic()
    {
        return $this->belongsTo(\App\Models\SysUser::class, 'pic_id');
    }

    public function members()
    {
        return $this->belongsToMany(\App\Models\SysUser::class, 'trans_assignments_members', 'assignment_id', 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\SysUser::class, 'trans_assignments_users', 'assignment_id', 'user_id');
    }
}
