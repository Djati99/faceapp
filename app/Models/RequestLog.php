<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    protected $table = 'fa_log';
    protected $primaryKey = "fa_log_id";
    public $timestamps = false;


    protected $fillable = [
        'transaction_type',
        'url',
        'params',
        'response_status',
        'response_message',
        'created_at'
    ];
    protected $casts = [
        'params' => 'array',
        'response_message' => 'array'
    ];    
}