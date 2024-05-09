<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use App\Models\BaseModel;
 

class Suspension  extends BaseModel
{
    use Translatable;

    protected $fillable = [
        'suspensionable_id',
        'suspensionable_type',
        'suspension_type',
    ];
    public $translatedAttributes = [
        'reason',
    ];

    public function suspensionable()
    {
        return $this->morphTo();
    }
}
