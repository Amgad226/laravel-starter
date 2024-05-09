<?php

namespace App\Models;

use Error;
use Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Auth\Entities\User;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Base model that all models in the system inherit from it
 */
class BaseModel  extends Model
{
    use HasFactory;

    //SECTION Main Operation - Functions
    public static function operationWhereHasOr($filter)
    {
        $base = isset($filter['base']) ? $filter['base'] : false;
        if ($base == 'and') {
            return 'whereHas';
        } elseif ($base == 'or') {
            return 'orWhereHas';
        }
    }

    public static function baseOperation($filter)
    {
        $base = isset($filter['base']) ? $filter['base'] : false;
        if (($base == 'has') || !$base) {
            return 'whereHas';
        }
        if ($base == 'hasNot') {
            return 'whereDoesntHave';
        }
        if ($base == 'in') {
            return 'whereIn';
        }
        if ($base == 'notIn') {
            return 'whereNotIn';
        }

        abort(400, "you must send (has|notHas|in|notIn");
    }

    public static function baseOperationHas(&$filter)
    {
        // TODO must move this check on operation to another place 
        if (!isset($filter['op'])) {
            $filter['op'] = "=";
        }

        $base = isset($filter['base']) ? $filter['base'] : false;
        if ($base == "has" || $base == false) {
            return 'whereHas';
        }
        if ($base == "hasNot") {
            return 'whereDoesntHave';
        }
        abort(400, 'you must send has or notHas');
    }

    public static function baseOperationIn($filter)
    {
        $base = isset($filter['base']) ? $filter['base'] : false;
        if ($base == "in") {
            return 'whereIn';
        }
        if ($base == "notIn") {
            return 'whereNotIn';
        }
        abort(400, 'you must send notIn or in');
    }
    public static function operationsIn($filter)
    {
        $base = isset($filter['op']) ? $filter['op'] : false;
        if ($base == "=") {
            return 'whereIn';
        }
        if ($base == "!=") {
            return 'whereNotIn';
        }
        abort(400, 'you must send( = || != ) in op');
    }
    public static function checkIfArray($value_to_check, $bool = true): mixed
    {
        if (is_null($value_to_check)) {
            abort(400, 'You must send a value.');
        }

        if ($bool) {
            if (!is_array($value_to_check)) {
                abort(400, 'Value must be an array.');
            }
            if ($value_to_check[0] == null) {
                abort(400, 'Value must be a non-empty array.');
            }
        } else {
            if (is_array($value_to_check)) {
                abort(400, 'Value cannot be an array.');
            }
        }

        return $value_to_check;
    }
    public static function checkIfInt($values): bool
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $value) {
            if (!preg_match('/^[1-9][0-9]*$/', $value)) {
                return false;
            }
        }
        return true;
    }
}
