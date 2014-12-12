<?php namespace Esensi\Core\Traits;

use \EsensiCoreRepositoryException as RepositoryException;
use \Illuminate\Support\Facades\Response;
use \Illuminate\Support\Facades\Input;

/**
 * Trait that handles redirects for API controllers
 *
 * @author daniel <daniel@bexarcreative.com>
 * @see \Esensi\Core\Contracts\ExceptionHandlerInterface
 */
trait ApiExceptionHandlerTrait{

    /**
     * Handles exceptions for API output
     *
     * @param RepositoryException $exception
     * @return array
     */
    protected function handleException(RepositoryException $exception)
    {
        $data    = Input::all();
        $errors  = $exception->getErrors();
        $message = $exception->getMessage();
        $code    = $exception->getCode() ? $exception->getCode() : 400;
        $content = array_filter(compact('errors', 'message', 'code', 'data'));
        return Response::json($content, $code);
    }

}