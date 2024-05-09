<?php 

namespace App\Notification\Notifications;

use App\Notification\Enums\NotificationTypeEnum;
use App\Notification\Notifications\Abstracts\FCMNotification;
use App\Models\Profile;

class ProfileApproveNotification extends FCMNotification{
    
    public $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
        $this->type = NotificationTypeEnum::NOTIFICATION->value;
    }

    public  function getMessages($locale = 'en'): array
    {
        return [
            'role_1' => [
                'title' => "Welcome To APP_NAME",
                'body' => 'Your account has been confirmed successfully, you can benefit from our services now.',
            ],
            'role_2' => [
                'title' => "Welcome To APP_NAME",
                'body' => 'Your account has been confirmed successfully, you can benefit from our services now.',
            ],
            'role_3' => [
                'title' => "Welcome To APP_NAME",
                'body' => 'Your account has been confirmed successfully, you can benefit from our services now.',
            ],
        ];
    }

    public  function generateData(): array
    {
        return [];
    }
}
?>