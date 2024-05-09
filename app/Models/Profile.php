<?php

namespace Modules\Profile\Entities;

use App\Interfaces\SuspensionableInterface;
use App\Traits\Suspensionable;
use App\Models\BaseModel;
use App\Scopes\SuspensionScope;
use App\Traits\Communicateable;
use Illuminate\Auth\Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Announcement\Entities\FreeSample;
use Modules\Announcement\Entities\SurveyAnswer;
use Modules\Auth\Entities\Role;
use Modules\Auth\Entities\User;
use Modules\Catalog\Entities\Brand;
use Modules\Catalog\Entities\Product;
use Modules\Interaction\Entities\Alert;
use Modules\Loyalty\Entities\Payment\Points\Wallets\ProfilePointWallet;
use Modules\Notification\Directors\ProfileFCMND;
use Modules\Notification\Notifications\ProfileApproveNotification;
use Modules\Operation\Entities\Prescription;
use Modules\Profile\CustomPivots\FreeSampleProfilePivot;
use Modules\Profile\Enums\ProfileStatusEnum;
use Illuminate\Support\Str;
use Modules\Loyalty\Entities\GiftOrder;
use Modules\Loyalty\Entities\Payment\Flags\Wallets\ProfileFlagWallet;
use Modules\Operation\Entities\DispenseItem;
use Modules\Operation\Entities\PrescriptionTransaction;

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

    // must use 
    public static $suspensions_status = [
        'user' => [
            ProfileStatusEnum::BLOCKED
        ], 'doctor' => [
            ProfileStatusEnum::PENDING,
            ProfileStatusEnum::REJECTED,
            ProfileStatusEnum::BLOCKED,
        ], 'brand_operator' => [
            ProfileStatusEnum::PENDING,
            ProfileStatusEnum::REJECTED,
            ProfileStatusEnum::BLOCKED,
        ],
        'pharmacist' => [
            ProfileStatusEnum::PENDING,
            ProfileStatusEnum::REJECTED,
            ProfileStatusEnum::BLOCKED,
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

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function profileInfo()
    {
        return $this->hasOne(ProfileInfo::class, 'profile_id');
    }
    public function workplaces()
    {
        return $this->belongsToMany(Workplace::class, 'profile_workplace')
            ->withPivot('id');
    }

    public function workplaceProfiles()
    {
        return $this->hasMany(ProfileWorkplace::class, 'profile_id',);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'medicine_shortcuts');
    }

    public function surveyAnswers()
    {
        return $this->hasMany(SurveyAnswer::class, 'profile_id');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'profile_id');
    }

    public function unExpiredAlerts()
    {
        return $this->alerts->where('expired_at', '>', now());
    }

    public function recommendors()
    {
        return $this->belongsToMany(User::class, 'profile_recommendations', 'profile_id', 'user_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function freeSamples()
    {
        return $this->belongsToMany(FreeSample::class)->using(FreeSampleProfilePivot::class)
            ->withPivot([
                'when_to_answer',
                'received',
                'received_at',
            ]);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_profile');
    }

    public function getBrandAttribute()
    {
        return $this->brands->first();
    }

    public function insurances()
    {
        return $this->morphToMany(Insurance::class, 'insuranceable', 'insuranceables', 'insuranceable_id', 'insurance_id');
    }

    public function pointWallets()
    {
        return $this->hasMany(ProfilePointWallet::class, 'profile_id');
    }
    public function flagWallets()
    {
        return $this->hasMany(ProfileFlagWallet::class, 'profile_id');
    }
    public function giftOrder()
    {
        return $this->hasMany(GiftOrder::class, 'profile_id');
    }
    public function dispenseItems()
    {
        return $this->hasMany(DispenseItem::class, 'profile_id');
    }
    public function prescriptionTransactions()
    {
        return $this->hasMany(PrescriptionTransaction::class, 'profile_id');
    }

    public function getIsRecommendedAttribute()
    {
        return ($this->whereHas('recommendors', function ($q) {
            $q->where([['user_id', auth()->user()->id], ['profile_id', $this->id]]);
        })->first() ? true : false);
    }

    public function getPointWalletBalanceAttribute()
    {
        return $this->pointWallets->sum('value') ?? 0;
    }

    public function getFlagWalletBalanceAttribute()
    {
        return $this->flagWallets->sum('value') ?? 0;
    }


    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    //!SECTION Scopes ----------------------------------------------------------------

    public function scopeSearch($query, $search)
    {
        if (isset($search)) {
            return $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            })
                ->orWhereHas('profileInfo', function ($query) use ($search) {
                    $query->where('bio', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%");
                })
                ->orWhereHas('role', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('workplaces', function ($query) use ($search) {
                    $query->whereTranslationLike('text', "%{$search}%");
                })
                ->orWhereHas('profileInfo.specialization', function ($query) use ($search) {
                    $query->whereTranslationLike('text', "%{$search}%");
                })
                ->orWhereHas('profileInfo.subSpecializations', function ($query) use ($search) {
                    $query->whereTranslationLike('text', "%{$search}%");
                });
        }

        return $query;
    }

    public function scopeWhereRegionId($query, $shape)
    {
        $base = self::baseOperationHas($shape);

        return $query->$base('user', function ($query) use ($shape) {
            $query->where('region_id', $shape['op'], $shape['value']);
        });
    }

    public function scopeWhereCityId($query, $shape)
    {
        $base = self::baseOperationHas($shape);

        return $query->$base('user.region', function ($query) use ($shape) {
            $query->where('city_id', $shape['op'], $shape['value']);
        });
    }

    public function scopeWhereProvince($query, $shape)
    {
        $value = self::checkIfArray($shape['value'] ?? null, false);
        $base = self::baseOperationHas($shape);

        return $query->$base('user.region.city', function ($query) use ($shape, $value) {
            $query->where('province_id', $shape['op'], $value);
        });
    }


    public function scopeWhereSpecializationIds($query, $shape)
    {
        $values = self::checkIfArray($shape['value'] ?? null, true);
        $base =  self::baseOperationHas($shape);

        return $query->$base('profileInfo', function ($query) use ($values) {
            $query->whereIn('spec_id', $values);
        });
    }

    public function scopeWhereSubspecializationIds($query, $shape)
    {
        $values = self::checkIfArray($shape['value'] ?? null, true);
        $base =  self::baseOperationHas($shape);

        return $query->$base('profileInfo.subSpecializations', function ($query) use ($values) {
            $query->whereIn('sub_spec_id', $values);
        });
    }

    public function scopeWhereInsuranceIds($query, $shape)
    {
        $values = self::checkIfArray($shape['value'] ?? null, true);
        $base = self::baseOperation($shape);

        return $query->$base('insurances', function ($query) use ($values) {
            $query->whereIn('insurance_id', $values);
        });
    }

    public function scopeWhereRole($query, $shape)
    {
        $value = Str::lower(self::checkIfArray($shape['value'] ?? null, false));
        $base = self::baseOperationHas($shape);

        return $query->$base('role', function ($query) use ($value, $shape) {
            $query->where('name', $shape['op'], $value);
        });
    }


    public function scopeWhereBrandId($query, $shape)
    {
        $value = self::checkIfArray($shape['value'] ?? null, false);
        $base = self::baseOperationHas($shape);
        return $query->$base('brands', fn ($q) => $q->where('brands.id', $value));
    }

    //REVIEW - To be reviewed in the future

    //TODO - Activate again if required in the future for suspended user   
    // public function scopeOrWhereHasOwner($query, User $user)
    // {
    //     return $query->orWhere('user_id', $user->id);
    // }


    public function scopeWhereDosentHaveSurveyAnswers($query, $free_sample)
    {
        return $query->whereHas('freeSamples', function ($query) use ($free_sample) {
            return $query->where('free_samples.id', $free_sample->id)->whereDoesntHave('survey.surveyAnswers');
        });
    }
    public function scopeWhereSurveyAnswered($query, $free_sample)
    {
        return $query->whereHas('freeSamples', fn ($q) => $q->where('free_samples.id', $free_sample->id))
            ->where('free_sample_profile.received', 1)
            ->whereHas('surveyAnswers', function ($query) use ($free_sample) {
                return $query->where('survey_id', $free_sample->survey?->id);
            });
    }

    public function scopeWhereSurveyNotReceived($query, $free_sample)
    {
        return $query->whereHas('freeSamples', fn ($q) => $q->where('free_samples.id', $free_sample->id))
            ->where('free_sample_profile.received', 0);
    }

    public function scopeWhereSurveyIgnored($query, $free_sample)
    {
        return $query->whereHas('freeSamples', fn ($q) => $q->where('free_samples.id', $free_sample->id))
            ->where('free_sample_profile.received', 1)
            ->where('free_sample_profile.when_to_answer', '<', now())
            ->whereDosentHaveSurveyAnswers($free_sample);
        // ->whereDoesntHave('surveyAnswers');
    }

    public function scopeWhereSurveyStillTrying($query, $free_sample)
    {
        return $query->whereHas('freeSamples', fn ($q) => $q->where('free_samples.id', $free_sample->id))
            ->where('free_sample_profile.received', 1)
            ->where('free_sample_profile.when_to_answer', '>=', now());
    }

    public function scopeWhereSurveyReceived($query, $free_sample)
    {
        return $query->whereHas('freeSamples', fn ($q) => $q->where('free_samples.id', $free_sample->id))
            ->where('free_sample_profile.received', 1);
    }

    public function scopeWhereFreeSampleStatus($query, $value = '', $free_sample)
    {

        switch ($value) {
            case 'answered':
                $query->whereSurveyAnswered($free_sample);
                break;
            case 'not_received':
                $query->whereSurveyNotReceived($free_sample);
                break;
            case 'ignored':
                $query->whereSurveyIgnored($free_sample);
                break;
            case 'still_trying':
                $query->whereSurveyStillTrying($free_sample);
                break;
            case 'received':
                $query->whereSurveyReceived($free_sample);
                break;
        }

        return;
    }



    //SECTION - Overrides
    public function unsuspend()
    {
        $this->alerts()->whereIn('reason_key', ['admin_block', 'admin_pending', 'admin_reject'])->delete();
        (new ProfileFCMND($this, new ProfileApproveNotification($this)))->send();
        $this->traitUnsuspend();
    }
}
