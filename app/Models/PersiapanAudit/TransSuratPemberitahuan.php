<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransSuratPemberitahuan extends Model
{
    protected $table = 'trans_surat_pemberitahuans';
    protected $fillable = [
        'tanggal_surat','nomor_surat','jenis_audit_id','company_id',
        'audit_object_id','surat_tugas_id','auditor_id','rev','status'
    ];
    protected $casts = ['tanggal_surat' => 'date'];

    public function jenisAudit(): BelongsTo { return $this->belongsTo(RefTypeAudit::class, 'type_id'); }
    public function perusahaan(): BelongsTo { return $this->belongsTo(RefOrgStructs::class, 'perusahaan_id'); }
    public function objekAudit(): BelongsTo { return $this->belongsTo(RefOrgStructs::class, 'object_id'); }
    public function suratTugas(): BelongsTo { return $this->belongsTo(TransAssignment::class, 'surat_tugas_id'); }
    public function auditor(): BelongsTo { return $this->belongsTo(SysUser::class, 'auditor_id'); }
}
