<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'propertytype', function ( Blueprint $table ) {
			$table->bigIncrements( 'id' )->unique();
			$table->string('old_id', 100)->nullable();
			$table->string( 'description' )->nullable();
			$table->integer( 'sort_sequence' )->nullable();
			$table->bigInteger( 'property_type_groupId' )->nullable();
			$table->string('code', 30)->nullable();
			$table->json('meta_data')->nullable();
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		//
	}

}
