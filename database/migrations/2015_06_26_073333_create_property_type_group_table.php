<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyTypeGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'property_type_group', function ( Blueprint $table ) {
			$table->bigIncrements( 'id' )->unique();
			$table->string('old_id', 100)->nullable();
			$table->string( 'description')->nullable();
			$table->integer( 'sequence_no')->nullable();
			$table->boolean( 'commercial')->nullable();
			$table->string('code', 30)->nullable();
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('property_type_group');
	}

}
