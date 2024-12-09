<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateZonesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'zones', function ( Blueprint $table ) {
			$table->bigIncrements( 'id' )->unique();
			$table->string('old_id', 100)->nullable();
			$table->string( 'description' )->nullable();
			$table->integer( 'sort_sequence' )->nullable();
			$table->integer( 'region' )->nullable();
			$table->integer('country_id')->nullable(); 
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop( 'zones' );
	}

}
