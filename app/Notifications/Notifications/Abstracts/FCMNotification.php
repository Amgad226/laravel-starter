<?php

namespace App\Notification\Notifications\Abstracts;

use App\Notification\Abstracts\Notification;

class FCMNotification implements Notification
{
    public $type;
    public $locale;
    public $role;
    public $os;

    public  function generateMessage($locale = 'en'): array{
        return $this->getMessages($locale)[$this->role];
    }
    
    public function getMessages() : array{
        return [];
    }

    public  function generateData() : array { 
        return [];
    }

    public function setLocale($locale = 'en')
    {
        $this->locale = $locale;
        return $this;
    }

    public function setRole($role )
    {
        $this->role = $role;
        return $this;
    }
}
