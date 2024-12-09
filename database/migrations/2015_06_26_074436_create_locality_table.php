<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocalityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'locality', function ( Blueprint $table ) {
			$table->bigIncrements('id')->unique();
			$table->string('old_id', 100)->unique();
			$table->string('locality_name')->nullable();
			$table->longText('description')->nullable();
			$table->integer('zoneId')->nullable();
			$table->integer('parent_locality_id')->nullable();
			$table->string('region', 100)->nullable();
			$table->string('post_code', 100)->nullable();
			$table->boolean('status')->default(0)->nullable();
			$table->integer( 'sequence_no' )->nullable();
			$table->integer('country_id')->nullable();
            $table->integer('region_id')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop( 'locality' );
	}

}
