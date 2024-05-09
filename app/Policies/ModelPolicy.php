<?php

namespace Modules\Category\Policies;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModelPolicy
{
    use HandlesAuthorization;

    public static function browse(User $user)
    {
        return $user->hasPermission('BaseModel.browse') && true;
    }

    public static function read(User $user, BaseModel $model)
    {
        return $user->hasPermission('BaseModel.read') && true;
    }

    public static function add(User $user)
    {
        return $user->hasPermission('BaseModel.add') && true;
    }

    public static function edit(User $user, BaseModel $model)
    {
        return $user->hasPermission('BaseModel.edit') && true;
    }

    public static function delete(User $user, BaseModel $model)
    {
        return $user->hasPermission('BaseModel.delete') && true;
    }
}
