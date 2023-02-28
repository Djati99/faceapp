<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessControlIn extends Model
{
    use HasFactory;

    protected $table = 'fa_accesscontrol_in';
    protected $primaryKey = "fa_accesscontrol_in_id";
    public $timestamps = false;
                
    protected $fillable = [
        'devicecode',
        'devicename',
        'channelId',
        'channelname',
        'alarmtypeid',
        'personid',
        'alarmtime',
        'firstname',
        'lastname',
        'accesstype',
        'created_at'
    ];
}