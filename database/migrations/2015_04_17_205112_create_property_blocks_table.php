<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePropertyBlocksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'property_blocks', function ( Blueprint $table ) {
			$table->unsignedBigInteger( 'id', false );
			$table->unsignedBigInteger( 'development_id', false );
			$table->string( 'short_description', 50 )->nullable();
			$table->string( 'title', 100 )->nullable();
			$table->text( 'long_description' )->nullable();
			$table->text( 'abstract' )->nullable();
			$table->text( 'latitude' )->nullable();
			$table->text( 'longitude' )->nullable();
			$table->primary( 'id' )->nullable();
			$table->foreign( 'development_id' )->references( 'id' )->on( 'developments' )->onDelete('cascade');
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop( 'property_blocks' );
	}

}
