<?php

namespace App\Models;

use App\Interfaces\SuspensionableInterface;
use App\Traits\Suspensionable;
use App\Models\BaseModel;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;

use App\Notification\Directors\ProfileFCMND;
use App\Notification\Notifications\ProfileApproveNotification;

use App\Enums\SuspensionStatusEnum;
use Illuminate\Support\Str;


class Profile  extends BaseModel implements SuspensionableInterface
{
    use Suspensionable,  HasApiTokens {
        unsuspend as traitUnsuspend;
    }

    protected $fillable = [
        'role_id',
        'user_id',
        'image',
    ];
    public static $suspensions_status = [
        'user' => [
            SuspensionStatusEnum::BLOCKED
        ], 'doctor' => [
            SuspensionStatusEnum::PENDING,
            SuspensionStatusEnum::REJECTED,
            SuspensionStatusEnum::BLOCKED,
        ], 'brand_operator' => [
            SuspensionStatusEnum::PENDING,
            SuspensionStatusEnum::REJECTED,
            SuspensionStatusEnum::BLOCKED,
        ],
        'pharmacist' => [
            SuspensionStatusEnum::PENDING,
            SuspensionStatusEnum::REJECTED,
            SuspensionStatusEnum::BLOCKED,
        ],
    ];

    public static function getSuspensionsStatusAttribute(): array
    {
        return self::$suspensions_status;
    }

    public static function validationOnStatusInChangeSuspensionStatus($status, $entity)
    {
        $role = $entity->role->name;
        if (!in_array($status, self::getSuspensionsStatusAttribute()[$role])) {
            abort(422, 'accept only these suspensions status (' . implode(',', self::getSuspensionsStatusAttribute()[$role]) . ') for profile');
        };
    }

    public static function  conditionBeforeChangeSuspensionStatus($entity)
    {
        if ($entity->role->name == 'admin') {
            abort(422, 'cannot change admin profile status ');
        }
    }

    public function changeSuspensionStatusAction($request)
    {
    }


    public function approveConditions($request)
    {
        return true;
    }
    public function approveAction($request)
    {
        return true;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    //SECTION Scopes ----------------------------------------------------------------

    public function scopeSearch($query, $search)
    {
        if (isset($search)) {
            return $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            })
                ->orWhereHas('role', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
        }
        return $query;
    }

    /* Scope filter
        shape = [
            "op"=>"= ,>, <, !=",
            "base"=>"has , hasNot , in ,notIn , or , and ",
            "value" => "any"
        ]
    */
    public function scopeWhereProvince($query, $shape)
    {
        $value = self::checkIfArray($shape['value'] ?? null, false);
        $base = self::baseOperationHas($shape);

        return $query->$base('user.region.city', function ($query) use ($shape, $value) {
            $query->where('province_id', $shape['op'], $value);
        });
    }



    // Normal scope
    public function scopeWhereDosentHaveSurveyAnswers($query, $free_sample)
    {
        return $query->whereHas('relation', function ($query) use ($free_sample) {
            return $query->where('table.id', $free_sample->id)->whereDoesntHave('relation.relation');
        });
    }


    //SECTION - Overrides
    public function unsuspend()
    {
        $this->alerts()->whereIn('reason_key', ['admin_block', 'admin_pending', 'admin_reject'])->delete();
        (new ProfileFCMND($this, new ProfileApproveNotification($this)))->send();
        $this->traitUnsuspend();
    }
}
