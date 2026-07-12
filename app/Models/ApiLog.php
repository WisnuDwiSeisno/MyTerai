<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    public $timestamps = false; // pakai created_at manual
    protected $table   = 'api_logs';

    protected $fillable = [
        'api_client_id',
        'method',
        'endpoint',
        'status_code',
        'ip_address',
        'request_body',
        'response_body',
        'duration_ms',
        'created_at',
    ];

    public function apiClient()
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }
}
