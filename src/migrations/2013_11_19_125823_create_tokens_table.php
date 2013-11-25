<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		Schema::create('tokens', function(Blueprint $table)
		{
            // Add table columns
            $table->increments('id');
            $table->string('type', 32);
            $table->string('token', 256);
            $table->timestamp('created_at');
            $table->timestamp('expires_at')->nullable();

            // Add table indexes and foreign keys
            $table->index('token');
            $table->index(['type', 'token'], 'type_token');

		});
		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tokens');
	}

}