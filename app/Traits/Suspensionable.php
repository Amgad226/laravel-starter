<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Suspensions\GeneralPendingSuspension;
use App\Abstracts\AbstractSuspension;
use Modules\Category\Entities\Suspension;
use Modules\Profile\Enums\SuspensionStatusEnum;

/**
 * Suspension trait that used with suspensionable traits
 */
trait Suspensionable
{
    protected static function bootSuspensionable(): void
    {
        //REVIEW if we want to return work this global scope must edit remove bootSuspensionable in Workplace model 
        //REVIEW - may be need this global scope
        // static::addGlobalScope(new SuspensionScope);
    }
    public function suspensions()
    {
        return $this->morphMany(Suspension::class, 'suspensionable');
    }

    public function hardSuspend($reason_en, $reason_ar, $type = 'pending')
    {
        $this->suspensions()->delete();
        $this->suspensions()->create([
            'suspension_type' => $type,
            'en' => ['reason' => $reason_en],
            'ar' => ['reason' => $reason_ar],
        ]);
        $this->update();
    }

    public function suspend(AbstractSuspension $suspension = new GeneralPendingSuspension())
    {
        //ANCHORE - if there is multi suspensions feature it needs to be fixed here
        $this->suspensions()->delete();
        $this->hardSuspend($suspension->getReason('en'), $suspension->getReason('ar'), $suspension->getType());
    }

    public function unsuspend()
    {
        $this->suspensions()->delete();
        $this->update();
    }

    public function isSuspended($type = 'pending')
    {
        return $this->suspensions->where('suspension_type', $type)->count();
    }

    public function getLatestSuspension()
    {
        return $this->suspensions->sortByDesc('created_at')->first();
    }
    public function getCurrentStatus()
    {
        return   $this->getLatestSuspension()
            ?  $this->getLatestSuspension()
            : (object)[
                'suspension_type' => 'active',
                'reason' => null,
                'translations' => null
            ];
    }


    // this filter can call from params  or call inside query in case $shape==false 
    public function scopeSuspended($query, $shape = null)
    {
        if (is_null($shape)) {
            return $query;
        }

        if ($shape === false) {
            return $query->whereDoesntHave('suspensions');
        }
        $base = self::baseOperationHas($shape);
        $value = self::checkIfArray($shape['value'] ?? null, false);

        $query->$base('suspensions', function ($inner_query) use ($shape, $value) {

            return $inner_query->where('suspension_type', $shape['op'], $value);
        });
    }
    public function scopeWhereStatusType($query, $shape = null)
    {
        $value = Str::lower(self::checkIfArray($shape['value'] ?? null, false));
        switch ($value) {
            case SuspensionStatusEnum::ACTIVE:
                return $query->suspended(false);
            case SuspensionStatusEnum::BLOCKED:
            case SuspensionStatusEnum::PENDING:
            case SuspensionStatusEnum::REJECTED:
                return $query->suspended($shape);
            default:
                abort(400, 'value must be (ACTIVE, BLOCKED, PENDING, REJECTED)');
        }
    }
}
