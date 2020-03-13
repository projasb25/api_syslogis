<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if (
            $exception instanceof TokenExpiredException ||
            $exception instanceof JWTException
        ) {
            return response()->json([
                'success' => false,
                'error' => [
                    'mensaje' => 'Invalid token.',
                    'code' => 3013
                ]
            ], 401);
        }

        // if (
        //     $exception instanceof Exception
        // ) {

        //     $response = [
        //         'success' => false,
        //         'error' => [
        //             'mensaje' => $exception->getMessage(),
        //             'code' => 3000,
        //         ]
        //     ];
        //     return response()->json($response, 500);
        // }

        return parent::render($request, $exception);
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        $errors = $exception->errors();
        $first_error = array_key_first($errors);
        return response()->json([
            'success' => false,
            'error' => [
                'message' => $errors[$first_error][0],
            ]
        ], $exception->status);
    }
}
