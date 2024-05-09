<?php


namespace App\Notification\Abstracts;


/**
 * Class NotificationDirector
 * A class that provides specific notification build logic 
 * It decides what users to send the notifications to.
 * @package App\Notification\Makers
 */
interface NotificationDirector 
{
    public function target();
    public function instanciateNotifications();
    public function send();
}
