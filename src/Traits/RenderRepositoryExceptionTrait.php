<?php namespace Esensi\Core\Traits;

use App\Exceptions\RepositoryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/**
 * Trait that renders RepositoryExceptions
 *
 * @package Esensi\Core
 * @author daniel <dalabarge@emersonmedia.com>
 * @copyright 2014 Emerson Media LP
 * @license https://github.com/esensi/core/blob/master/LICENSE.txt MIT License
 * @link http://www.emersonmedia.com
 * @see Esensi\Core\Contracts\RenderRepositoryExceptionInterface
 */
trait RenderRepositoryExceptionTrait {

    /**
     * Render a Repository Exception into an HTTP respons.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Exceptions\RepositoryException $e
     * @return \Illuminate\Http\Response
     */
    public function renderRepositoryException($request, RepositoryException $e)
    {
        // Get the controller that handled the request
        $action = Route::currentRouteAction();
        if(! empty($action))
        {
            // Render the exception according to the controller preference
            $action = explode('@', $action);
            $class = App::make(head($action));
            return $class->handleException($e);
        }

        // If this was a console command then no route/controller was used
        // so handle this exception like normal
        return parent::render($request, $e);
    }

}
