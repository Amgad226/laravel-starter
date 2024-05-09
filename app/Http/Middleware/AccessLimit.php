<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

/**
 * This middleware gives a layer of validation witch links to the cache system
 * to handle trial limits on a route
 */
class AccessLimit
{
    public function handle(Request $request, Closure $next, $multiplier, $blocked_status, $margin = 1)
    {
        $limit_key = 'rate_limit:' . $request->ip() . ':' . $request->route()->getName() . ':next_valid_time';

        $nextValidTime = Cache::get($limit_key);

        if ($nextValidTime > now()) {
            $waitTime = $nextValidTime->diffInSeconds(now());
            //Return the remaining time for the next try without the data of the actual response
            return apiErrorResponse(
                "You will be able to retry in : " . $nextValidTime->diffForHumans(now()),
                429,
                ['retry_in' => $waitTime],
            );
        }

        $response = $next($request);


        $counter_key = 'limit_counter:' . $request->ip() . ':' . $request->route()->getName();
        //If the status should be blocked and penalted on each trial, this code executes
        if ($response->getStatusCode() == $blocked_status) {
            $count = (int)Cache::get($counter_key, 0);
            //The margin considers allowing the blocked status to be called multiple times befor applying a penalty or increase it
            if (($count + 1) % $margin == 0) {
                $nextInterval =   $multiplier * (((($count + 1) / $margin) + 1) ** 2) * 10;
            } else {
                $nextInterval =   0;
            }
            $nextValidTime = now()->addSeconds($nextInterval);
            Cache::put($limit_key, $nextValidTime, $nextInterval);
            $count++;
            Cache::put($counter_key, $count, 60 * 60);
            $waitTime = $nextValidTime->diffInSeconds(now());

            $response_content = json_decode($response->getContent(), true);
            // Return the response of the actual requested resource, with additional parameter that express the time remains for the blocked status penalty
            return apiErrorResponse(
                $response_content['message'] ?? null,
                (int)$blocked_status,
                [
                    'retry_in' => $waitTime,
                    ...($response_content['other'] ?? [])
                ],

            );
        } else if ($response->getStatusCode() != 429) { //If the status is acceptable, forget the penalty cache
            Cache::forget($counter_key);
        }
        //If the status is 429, the response get returned with the additional attribute "retry_in", with the remaining time of the timer
        return $response;
    }
}
