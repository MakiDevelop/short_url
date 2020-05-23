<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
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
        // CSRF
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'msg'     => '抱歉，您操作時間似乎已過期。 請再試一遍。',
                ]);
            }
            return redirect()
                ->back()
                ->withErrors(['抱歉，您操作時間似乎已過期。 請再試一遍。']);
        }

        return parent::render($request, $exception);
    }
}
