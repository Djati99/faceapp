<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'fa_attendance';
    protected $primaryKey = "fa_attendance_id";
    public $timestamps = false;
                
    protected $fillable = [
        'personnelCode',
        'personnelName',
        'deptName',
        'cardNumber',
        'swipeLocation',
        'eventName',
        'swipeTime',
        'created_at'
    ];
}