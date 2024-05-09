<?php

namespace App\Traits;

use App\Models\BaseModel;


/**
 * This trait used with models that are communicateAble 
 * it contains alerts and can be also other communication systems
 */
trait Communicateable{

    public function polyAlerts()
    {
        return $this->morphMany(BaseModel::class, 'alertable');
    }

    public function polyAlert($profile_id, $message_en, $message_ar, $reason_key = "unknown_reason", $type , $appearance_type , $dismissibility, $expired_at = null){
        $this->polyAlerts()->create([
            'profile_id' => $profile_id,
            'en' => ['text' => $message_en],
            'ar' => ['text' => $message_ar],
            'type' => $type->name,
            'dismissibility' => $dismissibility->name,
            'appearance_type' => $appearance_type->name,
            'reason_key' => $reason_key,
            'expired_at' => $expired_at
        ]);
        $this->update();
    }
}
?>
