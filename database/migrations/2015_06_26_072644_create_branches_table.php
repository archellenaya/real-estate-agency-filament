<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'branches', function ( Blueprint $table ) {
			$table->bigInteger('id')->unique(); 
			$table->string( 'name', 100 )->nullable();
			$table->string( 'slug', 100 )->nullable();
			$table->string( 'email', 50 )->nullable(); 
			$table->string( 'contact_number', 30 )->nullable();  
			$table->string( 'address' )->nullable();
			$table->string( 'coordinates' )->nullable();  
			$table->integer('display_order')->nullable();
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('branches');
	}

}
