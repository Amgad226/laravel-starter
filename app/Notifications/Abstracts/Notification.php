<?php
namespace App\Notification\Abstracts;
/**
 * Interface Notifier
 * An interface of classes responsible for choosing notification messages and configurations depending on the user role and entity
 * @package App\Notification\Contracts
 */
Interface Notification{
    public  function generateMessage() : array;
    public function getMessages() : array;
    public  function generateData() : array;
    public function setLocale($locale);
}
