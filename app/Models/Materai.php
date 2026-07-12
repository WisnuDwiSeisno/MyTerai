<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materai extends Model
{
    protected $table = 'materai';

    protected $fillable = [
        'api_client_id',
        'nomor_seri',
        'jenis',
        'nama_file',
        'path_file',
        'status',
        'duplikat_dari_id',
        'duplikat_count',
        'confidence',
        'raw_text',
        'qr_image_url',
    ];

    protected $casts = [
        'duplikat_count' => 'integer',
        'confidence' => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────

    // Materai ini diupload oleh client mana
    public function apiClient()
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }

    // Jika ini duplikat → tunjuk ke materai PERTAMA (yang asli)
    public function duplikatDari()
    {
        return $this->belongsTo(Materai::class, 'duplikat_dari_id');
    }

    // Di materai PERTAMA → lihat semua yang menduplikatnya
    public function duplikatList()
    {
        return $this->hasMany(Materai::class, 'duplikat_dari_id');
    }

    // ── Scope ───────────────────────────────────────────

    public function scopeValid($q)
    {
        return $q->where('status', 'unik');
    }
    public function scopeDuplikat($q)
    {
        return $q->where('status', 'duplikat');
    }
    public function scopeTidakTerbaca($q)
    {
        return $q->where('status', 'tidak_terbaca');
    }
}
