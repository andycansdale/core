<?php namespace Esensi\Core\Controllers;

use Esensi\Core\Contracts\ExceptionHandlerInterface;
use Esensi\Core\Contracts\PackagedInterface;
use Esensi\Core\Contracts\RepositoryInjectedInterface;
use Esensi\Core\Exceptions\RepositoryException;
use Esensi\Core\Models\Collection as Collection;
use Esensi\Core\Repositories\Repository;
use Esensi\Core\Traits\ApiExceptionHandlerTrait;
use Esensi\Core\Traits\PackagedTrait;
use Esensi\Core\Traits\RepositoryInjectedTrait;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;

/**
 * Controller for accessing repositories as an API
 *
 * @package Esensi\Core
 * @author daniel <dalabarge@emersonmedia.com>
 * @author diego <diego@emersonmedia.com>
 * @copyright 2014 Emerson Media LP
 * @license https://github.com/esensi/core/blob/master/LICENSE.txt MIT License
 * @link http://www.emersonmedia.com
 */
class ApiController extends Controller implements
    ExceptionHandlerInterface,
    PackagedInterface,
    RepositoryInjectedInterface {

    /**
     * Allow controller to dispatch commands.
     *
     * @see Illuminate\Foundation\Bus\DispatchesCommands
     */
    use DispatchesCommands;

    /**
     * Validate requests prior to calling the controller methods.
     *
     * @see Illuminate\Foundation\Validation\ValidatesRequests
     */
    use ValidatesRequests;

    /**
     * Make exceptions return a standard API exception format
     *
     * @see Esensi\Core\Traits\ApiExceptionHandlerTrait
     */
    use ApiExceptionHandlerTrait;

    /**
     * Package this controller
     *
     * @see Esensi\Core\Traits\PackagedTrait
     */
    use PackagedTrait;

    /**
     * Make use of Repository injection
     *
     * @see Esensi\Core\Traits\RepositoryInjectedTrait
     */
    use RepositoryInjectedTrait;

    /**
     * Inject dependencies
     *
     * @param Esensi\Core\Repositories\Repository $repository
     * @return Esensi\Core\Controllers\ApiController
     */
    public function __construct(Repository $repository)
    {
        $this->setupLayout();
        $this->setRepository($repository);
        //$this->beforeFilter('@filterRequest');
    }

    /**
     * Binds error handlers for exceptions
     *
     * @param Route $route
     * @param Request $request
     * @return mixed
     */
    public function filterRequest($route, $request)
    {
        $class = $this;

        App::error(function(RepositoryException $exception, $code, $fromConsole) use ($class)
        {
            return $class->handleException($exception);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return Illuminate\Pagination\Paginator
     */
    public function index()
    {
        $filters = Input::only('max', 'order', 'sort', 'keywords', 'trashed', 'ids');
        $repository = $this->getRepository();
        $repository->mergeFilters($filters);
        $paginator = $repository->index();
        return $paginator;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Esensi\Core\Models\Model
     */
    public function store()
    {
        return $this->getRepository()
            ->store(Input::all());
    }

    /**
     * Display the specified resource.
     *
     * @param integer $id of resource
     * @return Esensi\Core\Models\Model
     */
    public function show($id)
    {
        return $this->getRepository()
            ->show($id);
    }

    /**
     * Display the specified resource.
     *
     * @example $api->showWithRelated($id, 'related1+related2+relatedN')
     *
     * @param integer $id of resource
     * @param  string $relationship to load on the resource
     * @return Esensi\Core\Models\Model
     */
    public function showWithRelated($id, $relationship)
    {
        $relationship = Collection::parseMixed($relationship, [',', '+'])->all();
        return $this->getRepository()
            ->findWithRelated($id, $relationship);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param integer $id of resource to update
     * @return Esensi\Core\Models\Model
     */
    public function update($id)
    {
        return $this->getRepository()
            ->update($id, Input::all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param integer $id of resource to remove
     * @return boolean
     */
    public function delete($id)
    {
        return $this->getRepository()
            ->delete($id);
    }

    /**
     * Alias for delete method
     *
     * @param integer $id of resource to remove
     * @return boolean
     */
    public function destroy($id)
    {
        return $this->delete($id);
    }

    /**
     * Truncate the resources in storage.
     *
     * @return boolean
     */
    public function truncate()
    {
        return $this->getRepository()
            ->truncate();
    }

    /**
     * Retrieve the specified resource out of storage.
     *
     * @param integer $id of resource to retrieve
     * @return Esensi\Core\Models\Model
     */
    public function retrieve($id)
    {
        return $this->getRepository()
            ->retrieve($id);
    }

    /**
     * Trash the specified resource in storage.
     *
     * @param integer $id of resource to trash
     * @return boolean
     */
    public function trash($id)
    {
        return $this->getRepository()
            ->trash($id);
    }

    /**
     * Restore the specified resource in storage.
     *
     * @param integer $id of resource to restore
     * @return boolean
     */
    public function restore($id)
    {
        return $this->getRepository()
            ->restore($id);
    }

    /**
     * Purge the trashed resources from storage.
     *
     * @return boolean
     */
    public function purge()
    {
        return $this->getRepository()
            ->purge();
    }

    /**
     * Recover the trashed resources in storage.
     *
     * @return boolean
     */
    public function recover()
    {
        return $this->getRepository()
            ->recover();
    }

    /**
     * Perform a bulk action on an array of resources.
     *
     * @param string $action
     * @return integer
     * @throws BadMethodCallException
     */
    public function bulkAction($action)
    {
        // Get the bulk action to be called
        $class = get_called_class();
        $method = 'bulk' . ucfirst(studly_case($action));

        // Handle missing bulk actions
        if( ! method_exists($class, $method) )
        {
            throw new BadMethodCallException('Method ' . $method . ' does not exist on called class '. $class . '.');
        }

        // Call the bulk action and pass in the resource's IDs
        $ids = Input::get('ids', []);
        $response = call_user_func_array([$class, $method], [$ids]);

        // Redirect back with message
        return $response;
    }

    /**
     * Bulk delete the specified resources in storage.
     *
     * @param string|array $ids
     * @return integer
     */
    public function bulkDelete($ids)
    {
        return $this->getRepository()
            ->bulkDelete($ids);
    }

    /**
     * Bulk delete the specified resources in storage.
     *
     * @param string|array $ids
     * @return integer
     */
    public function bulkRestore($ids)
    {
        return $this->getRepository()
            ->bulkRestore($ids);
    }

    /**
     * Bulk delete the specified resources in storage.
     *
     * @param string|array $ids
     * @return integer
     */
    public function bulkTrash($ids)
    {
        return $this->getRepository()
            ->bulkTrash($ids);
    }

}
