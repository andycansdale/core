<?php namespace Alba\User\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

use Alba\Core\Controllers\Controller;
use Alba\User\Controllers\TokensResource;
use Alba\User\Controllers\TokensApiController;

/**
 * Controller for accessing TokensResource from a web interface
 *
 * @author daniel <daniel@bexarcreative.com>
 * @see Alba\Core\Controllers\Controller
 * @see Alba\User\Controllers\TokensResource
 * @see Alba\User\Controllers\TokensApiController
 */
class TokensController extends Controller {

    /**
     * The module name
     * 
     * @var string
     */
    protected $module = 'token';

    /**
     * Inject dependencies
     *
     * @param TokensResource $tokensResource
     * @param TokensApiController $tokensApi
     * @return void
     */
    public function __construct(TokensResource $tokensResource, TokensApiController $tokensApi)
    {   
        $this->resources['token'] = $tokensResource;
        $this->apis['token'] = $tokensApi;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index()
    {
        $paginator = $this->apis['token']->index();
        $collection = $paginator->getCollection();
        $this->content('index', compact('paginator', 'collection'));
    }
    
}