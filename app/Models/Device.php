<?php

namespace App\Models;

class Device  extends BaseModel
{
    protected $fillable = [
        'profile_id',
        'device_id',
        'fcm_token',
        'locale',
        'last_login_at',
    ];
    //SECTION - Relations -------------------------------------------------------------------------------------------------------
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
