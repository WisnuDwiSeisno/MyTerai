<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    protected $table = 'api_clients';

    protected $fillable = [
        'nama',
        'api_key',
        'is_active',
        'keterangan',
        'last_used_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // Generate API key unik otomatis
    public static function generateKey(string $prefix = 'sk'): string
    {
        return $prefix . '_' . Str::random(32);
    }

    // Relasi: satu client bisa upload banyak materai
    public function materais()
    {
        return $this->hasMany(Materai::class, 'api_client_id');
    }

    // Relasi: log semua request dari client ini
    public function logs()
    {
        return $this->hasMany(ApiLog::class, 'api_client_id');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
