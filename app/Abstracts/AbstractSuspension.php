<?php

namespace App\Abstracts;
/**
 * Abstract class representing a suspension.
 */
abstract class AbstractSuspension
{
    /**
     * The reason for the suspension in English.
     *
     * @var string
     */
    protected $reason_en;

     /**
     * The reason for the suspension in Arabic.
     *
     * @var string
     */
    protected $reason_ar;
    
    /**
     * The type of the suspension.
     *
     * @var mixed
     */
    protected $type;

    /**
     * Get the reason for the suspension in the specified locale.
     *
     * @param string $locale The locale for the reason (either 'en' or 'ar').
     *
     * @return string The reason for the suspension in the specified locale.
     */
    public function getReason($locale){
        return $this->{"reason_$locale"};
    }

     /**
     * Get the type of the suspension.
     *
     * @return mixed The type of the suspension.
     */
    public function getType(){
        return $this->type;
    }
}
?>
