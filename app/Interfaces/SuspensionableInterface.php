<?php

namespace App\Interfaces;

interface SuspensionableInterface
{

    public static function getSuspensionsStatusAttribute(): array;
    public static function  conditionBeforeChangeSuspensionStatus($entity_id);
    public static function  validationOnStatusInChangeSuspensionStatus($status, $entity);
    public function changeSuspensionStatusAction($request);
    public function approveConditions($request);
    public function approveAction($request);
}
