<?php

namespace App\Models\Rencana_Biaya;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Rencana_Biaya\TransRencanaBiaya;
use App\Models\SysUser;

class TransRencanaBiayaCc extends Model
{
    protected $table = 'trans_rencana_biaya_cc';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'rencana_biaya_id',
        'cost_center_id',
    ];
    protected $casts = [
        'rencana_biaya_id' => 'int',
        'cost_center_id'   => 'int',
    ];

    public function rencana(): BelongsTo
    {
        return $this->belongsTo(TransRencanaBiaya::class, 'rencana_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'user_id');
    }
}
