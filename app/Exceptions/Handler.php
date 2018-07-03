<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Contracts\Container\Container;
use App\Services\LineService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        NotFoundHttpException::class,
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

    protected $lineService;

    public function __construct(Container $container, LineService $lineService)
    {
        parent::__construct($container);
        $this->lineService = $lineService;
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
        $class = get_class($exception);

        if (in_array($exception->getMessage(), \Config::get('constants.EXCEPTION_NO_REPORT')) || in_array($class, $this->dontReport)) {
            return;
        }

        $messageDebug = PHP_EOL . '!@!@ Exception Error !@!@' . PHP_EOL;
        $messageDebug .= PHP_EOL . 'Class: ' . $class . PHP_EOL;
        $messageDebug .= PHP_EOL . 'Stage: ' . getenv('STAGE') . PHP_EOL;
        $messageDebug .= PHP_EOL . 'Message: ' . $exception->getMessage() . PHP_EOL;
        $messageDebug .= PHP_EOL . 'File: ' . $exception->getFile() . PHP_EOL;
        $messageDebug .= PHP_EOL . 'Line: ' . $exception->getLine() . PHP_EOL;
        $this->lineService->sendDebugMessage($messageDebug);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
            switch (get_class($e->getPrevious())) {
                case \Tymon\JWTAuth\Exceptions\TokenExpiredException::class:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token has expired'
                    ], $exception->getStatusCode());
                case \Tymon\JWTAuth\Exceptions\TokenInvalidException::class:
                case \Tymon\JWTAuth\Exceptions\TokenBlacklistedException::class:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token is invalid'
                    ], $exception->getStatusCode());
                default:
                    break;
            }
        }
        return parent::render($request, $exception);
    }
}
