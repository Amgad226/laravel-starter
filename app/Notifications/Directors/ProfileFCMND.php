<?php

namespace App\Notification\Directors;

use App\Notification\Directors\Abstracts\FCMNotificationDirector;
use App\Notification\Notifications\Abstracts\FCMNotification;
use App\Models\Profile;

class ProfileFCMND extends FCMNotificationDirector
{

    protected $profile;

    public function __construct(Profile $profile, FCMNotification $notification)
    {
        $this->notification = $notification;
        $this->profile = $profile;
    }

    
    // NOTE This function targets the entities related to the current entity 
    // regardless of the actual notification
    public function target()
    {
        // Pick the related profiles
        $this->profiles = collect([$this->profile])
        ->merge(Profile::whereHas('role', function($q){
            return $q->where('roles.name', 'admin');
        })->get());
        return $this;
    }
}
