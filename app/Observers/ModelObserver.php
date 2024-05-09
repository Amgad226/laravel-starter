<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * Observe events on model's methods
 * used for logging data
 */
class ModelObserver
{
    /**
     * Create the observer
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function created(Model $model)
    {
    }

    public function updated(Model $model)
    {
    }

    public function deleting(Model $model)
    {
    }

    public function retrieved($model)
    {
    }
}
