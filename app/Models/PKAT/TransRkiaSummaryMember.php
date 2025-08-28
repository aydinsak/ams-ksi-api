<?php

namespace App\Models\PKAT;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SysUser;
use App\Models\PKAT\TransRkiaSummary;

class TransRkiaSummaryMember extends Model
{
    protected $table = 'trans_rkia_summary_members';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'summary_id',
        'user_id',
    ];

    protected $casts = [
        'summary_id' => 'int',
        'user_id'    => 'int',
    ];

    public function summary(): BelongsTo
    {
        return $this->belongsTo(TransRkiaSummary::class, 'summary_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'user_id');
    }
}
