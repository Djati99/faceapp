<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'fa_setting';
    protected $primaryKey = "fa_setting_id";
    public $timestamps = false;
                
    protected $fillable = [
        'startdate',
        'enddate',
        'ip_server_fr',
        'ip_clock_in',
        'ip_clock_out',
        'unit_name',
        'created_at'
    ];
}