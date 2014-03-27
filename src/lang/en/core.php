<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Error messages
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the Core module.
	|
	*/

	'errors' => [
		'store' => 'Object could not be stored.',
		'show' => 'Object could not be found.',
		'update' => 'Object could not be updated.',
		'restore' => 'Object could not be restored.',
		'destroy' => 'Object could not be deleted.',
		'trashing' => 'Object cannot be sent to trash.',
		'rate_limit_exceeded' => 'Not so fast El Guapo! Your rate limit has been exceeded so you\'ve been put into a :timeout minute timeout.',
	],


	/*
	|--------------------------------------------------------------------------
	| Status message lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain status message lines used by the
	| Core module models.
	|
	*/

	'messages' => [
		'never_expires'	=> 'Never Expires',
		'never_updated'	=> 'Never Updated',
		'never_created' => 'Never Created',
		'never_deleted' => 'Never Deleted',
		'rate_limit_exceeded' => 'Rate Limit Exceeded',
	],
];