<?php

namespace App\Exceptions;

use App\Helpers\Notification;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Error;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                $message = $e->getMessage();
                // if the message is the laravel 404 message replace it with not found $model_name
                if (Str::startsWith($message, 'No query results for model')) {
                    $lastPart = Str::afterLast($message, '[');
                    $parts = explode('\\', $lastPart);
                    $model_name = end($parts);
                    $message = "not found [{$model_name}";
                }
                // else if its a custom message return it
                return apiErrorResponse(
                    $message,
                    404,
                    (object)[],
                );
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return apiErrorResponse(
                __('unauthorized'), //This action is unauthorized.
                403,
                (object)[],
            );
        }

        // if ($exception instanceof Error) 
        // {
        //     $message = '<users/112160228934512137324> 500 Server Error: ' . $exception->getMessage();
        //     Notification::sendToGoogleWorkspace($message, config('googleworkspace.errors_url'));
        // }


        return parent::render($request, $exception);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        return apiErrorResponse(
            $e->getMessage(),
            $e->status,
            $e->errors(),
        );
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return apiErrorResponse(
            $exception->getMessage(),
            401,
            (object)[],
        );
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        if ($e instanceof HttpExceptionInterface) {
            $message = $e->getMessage();
            $code = $e->getStatusCode();
        } else {
            $message = app()->environment(['local', 'testing', 'dev', 'development']) ? $e->getMessage() : 'Server Error';
            $code = 500;
        }
        return apiErrorResponse(
            $message,
            $code,
            (object)[],

        );
    }
}
