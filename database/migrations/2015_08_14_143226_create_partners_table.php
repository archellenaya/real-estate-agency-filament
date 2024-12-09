<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'partners', function ( Blueprint $table ) {
			$table->string( 'id', 50 )->unique();
			$table->string( 'name', 100 )->nullable();
			$table->string( 'email' )->nullable();
			$table->text( 'address' )->nullable();
			$table->string( 'country' )->nullable();
			$table->string( 'post_code', 20 )->nullable();
			$table->string( 'phone_1', 30 )->nullable();
			$table->string( 'phone_2', 30 )->nullable();
			$table->string( 'fax', 30 )->nullable();
			$table->text( 'summary' )->nullable();
			$table->string( 'logo_file_name' )->nullable();
			$table->string( 'logo_link', 200 )->nullable();
			$table->enum( 'partner_type', [ 'Everything', 'For Sale', 'To Let' ] )->nullable();
			$table->string( 'template' )->nullable();
			$table->boolean( 'active' )->default( TRUE );
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop( 'partners' );
	}

}
