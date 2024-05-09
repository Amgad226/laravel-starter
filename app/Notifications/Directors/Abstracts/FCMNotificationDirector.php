<?php


namespace App\Notification\Directors\Abstracts;

use App\Models\Device;
use App\Notification\Abstracts\NotificationDirector;
use App\Notification\Senders\FCMSender;

/**
 * Class NotificationDirector
 * A class that provides specific notification build logic 
 * It decides what users to send the notifications to.
 * @package App\Notification\Makers
 */
abstract class FCMNotificationDirector implements NotificationDirector
{
    protected $notification;
    protected $profiles;

    public function instanciateNotifications(): array
    {
        // Assign their devices to the instance
        $devices = Device::whereIn('profile_id', $this->profiles->pluck('id')->toArray())->with(['profile.role'])->get();
        $roles = array_keys($this->notification->getMessages());
        $notifications = [];
        foreach ($roles as $role) {
            $notifications[] = [
                'notification' => (clone $this->notification)->setLocale('en')->setRole($role),
                'devices' => $devices->where('profile.role.name', $role)->where('locale', 'en')
            ];
            $notifications[] =  [
                'notification' => (clone $this->notification)->setLocale('ar')->setRole($role),
                'devices' => $devices->where('profile.role.name', $role)->where('locale', 'ar')
            ];
        }
        return $notifications;
    }

    public function send()
    {
        $this->target();
        $notifications = $this->instanciateNotifications();
  
        foreach ($notifications as $instance) {
            if(isset($instance['devices']))
            FCMSender::send($instance['notification'], $instance['devices']);
        }
    }
}
