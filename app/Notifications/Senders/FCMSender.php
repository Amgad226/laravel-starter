<?php


namespace App\Notification\Senders;

use Illuminate\Support\Collection;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\Notification;

use Kreait\Firebase\Messaging\CloudMessage;
use App\Notification\Enums\NotificationTypeEnum;
use App\Notification\Enums\OSEnum;
use App\Notification\Notifications\Abstracts\FCMNotification;

/**
 * Class NotificationSender
 * An action class that executes the firebase functionalities and send messages with the provided data to the users according the their device ids
 * @package App\Notification
 */
/**
 * in this class handle sending the notification by any package or by http request
 * here i use kreait/laravel-firebase package
 */
class FCMSender
{
    public static function send(FCMNotification $notification, Collection $devices)
    {
        $messaging = app('firebase.messaging');
        $account = config('firebase.projects.app.credentials.file');
        (new Factory)->withServiceAccount($account);


        $firebase_notification = Notification::fromArray($notification->generateMessage(getLangFromHeader()));
        $message = CloudMessage::new();

        if ($notification->os == OSEnum::IOS) {
            $apn = ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'badge' => 100,
                        'sound' => 'default',
                    ],
                ],
            ]);
            $message = $message->withApnsConfig($apn);
        }

        $data = $notification->generateData();
        $data['type'] = $notification->type;

        if ($notification->type == NotificationTypeEnum::NOTIFICATION->value) {
            $message = $message->withNotification($firebase_notification);
        } else if ($notification->type == NotificationTypeEnum::AUTHENTICATION->value) {
            $data['token'] = 'tsetaatetsts';
        }
        
        $message = $message->withData($data);

        $tokens = $devices->pluck('fcm_token')->toArray();

        if (count($tokens) > 0 && isset($tokens[0])) {
            $save = $messaging->sendMulticast(
                $message,
                $tokens
            );
        }
    }
}
