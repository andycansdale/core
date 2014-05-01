<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Configuration values for Esensi\Core components package
	|--------------------------------------------------------------------------
	|
	| The following lines contain the default configuration values for the
	| Esensi\Core components package. You can publish these to your project for
	| modification using the following Artisan command:
	|
	| php artisan config:publish esensi/core
	|
	*/

	/*
	|--------------------------------------------------------------------------
	| Application aliases
	|--------------------------------------------------------------------------
	|
	| The following configuration options allow the developer to map aliases to
	| controllers and models for easier customization of how Esensi handles
	| requests related to this components package. These aliases are loaded by
	| the service provider for this components package.
	|
	*/

	'aliases' => [
		'EsensiCoreModel'				=> '\Esensi\Core\Models\Model',
		'EsensiCoreResource'			=> '\Esensi\Core\Resources\Resource',
		'EsensiCoreResourceException'	=> '\Esensi\Core\Exceptions\ResourceException',
		'EsensiCoreResourceInterface'	=> '\Esensi\Core\Contracts\ResourceInterface',
		'EsensiCoreController'			=> '\Esensi\Core\Controllers\Controller',
		'EsensiCoreApiController'		=> '\Esensi\Core\Controllers\ApiController',
		'EsensiCoreAdminController'		=> '\Esensi\Core\Controllers\AdminController',
		'EsensiCoreSeeder'				=> '\Esensi\Core\Seeders\Seeder',
		'EsensiCoreModuleProvider'		=> '\Esensi\Core\Providers\ModuleServiceProvider',
		'EsensiCoreRateLimiter'			=> '\Esensi\Core\Middlewares\RateLimiter',
	],

	/*
	|--------------------------------------------------------------------------
	| Component packages to load
	|--------------------------------------------------------------------------
	|
	| The following configuration options tell Esensi which component packages
	| are available. This can be useful for many things but is specifically used
	| by the template engine to determine how to render the administrative UI.
	|
	*/

	'modules' => [
		// 'FooBar'
	],

	/*
	|--------------------------------------------------------------------------
	| Configuration of component package route prefixes
	|--------------------------------------------------------------------------
	|
	| The following configuration options alter the route prefixes used for
	| the administrative backend, API, and component package URLs.
	|
	*/

	'prefixes' => [
		'api' => [
			'latest'	=> 'api',
			'v1' 		=> 'api/v1',
		],
		'backend'		=> 'admin',
		'modules' => [
			'users'			=> 'users',
			'tokens'		=> 'tokens',
			'roles'			=> 'roles',
			'permissions'	=> 'permissions',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Routes to be included by all component packages
	|--------------------------------------------------------------------------
	|
	| The following configuration options alter which routes are included,
	| effectively allowing the user to not use some or all of the default
	| routes available.
	|
	*/

	'routes' => [
		
		'api' 		=> false,
		'backend' 	=> true,
		'public'	=> true,
	],

	/*
	|--------------------------------------------------------------------------
	| Package to be used by core module
	|--------------------------------------------------------------------------
	|
	| The following configuration option alter which package namespace is used
	| for all of the views. Set to empty to use the application level views.
	|
	*/

	'package' => 'esensi::',
	
	/*
	|--------------------------------------------------------------------------
	| Views to be used by core module
	|--------------------------------------------------------------------------
	|
	| The following configuration options alter which package handles the
	| views, and which views are used specifically by each function.
	|
	*/

	'views' => [

		// Error pages
		'missing'			=> 'esensi::core.missing',
		'whoops'			=> 'esensi::core.whoops',

		// Modals
		'modal'				=> 'core.modal',
	],

	/*
	|--------------------------------------------------------------------------
	| Dashboard link
	|--------------------------------------------------------------------------
	|
	| The following configuration option specifies whether the backend should
	| show or hide the dashboard menu item.
	|
	*/

	'dashboard' => false,

	/*
	|--------------------------------------------------------------------------
	| Rate limit settings
	|--------------------------------------------------------------------------
	|
	| The following configuration option specifies whether or not the rate
	| limiter should be enabled and how it should behave. The default behavior
	| is that it is enabled and set to reasonable levels to control
	| potential hacking threats. More than 10 requests to the same page per
	| minute will generate a 10 minute ban for that IP address.
	|
	*/

	'rates' => [

		// Should the limiter be enabled?
		'enabled' => true,

		// Should limits be based on unique routes?
		'routes' => true,

		// Request per period
		'limit' => 10,

		// Period duration in minutes
		'period' => 1,

		// Cache settings
		'cache' => [

			// Namespace for tags
			'tag' => 'xrate',

			// Cache storage settings
			'driver' => 'file',
			'table' => 'cache',
			
			// Timeout (in minutes) an IP should be blocked
			'timeout' => 10,
		],
	],
	
];