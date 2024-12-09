<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('properties', function(Blueprint $table)
		{  
			$table->string( 'id', 50 )->unique();
			$table->string( 'old_id', 100 );
			$table->string('slug', 100)->nullable();
			$table->string( 'property_ref_field', 50 );
			$table->string('market_status_field', 20)->nullable(); 
			$table->timestamp( 'expiry_date_time' )->nullable();
			$table->string('market_type_field', 20)->nullable(); 
			$table->boolean( 'commercial_field' )->default(false);
			$table->integer( 'locality_id_field' )->nullable();
			$table->integer( 'property_type_id_field' )->nullable();
			$table->integer( 'property_status_id_field' )->nullable();
			$table->decimal( 'price_field', 12, 2)->nullable();
			$table->decimal( 'old_price_field', 12, 2)->nullable();
			$table->decimal( 'premium_field', 12, 2)->nullable();
			$table->string('rent_period_field', 30)->nullable();
			$table->timestamp( 'date_available_field' )->nullable();
			$table->boolean( 'por_field' )->default(false)->nullable(); // price on request
			$table->longText( 'description_field' )->nullable();
			$table->string( 'title_field', 200 )->nullable();
			$table->longText( 'long_description_field' )->nullable();
			$table->text( 'specifications_field' )->nullable();
			$table->text( 'items_included_in_price_field' )->nullable();
			$table->boolean( 'sole_agents_field' )->default(false)->nullable();
			$table->unsignedBigInteger( 'property_block_id_field' )->nullable();
			$table->foreign( 'property_block_id_field' )->references( 'id' )->on( 'property_blocks' );
			$table->integer( 'bedrooms_field' )->nullable();
			$table->integer( 'bathrooms_field' )->nullable();
			$table->string( 'contact_details_field', 100 )->nullable();
			$table->boolean( 'is_property_of_the_month_field' )->default(false);
			$table->boolean( 'is_featured_field' )->default(false);
			$table->boolean( 'is_hot_property_field' )->default(false)->nullable();
			$table->timestamp( 'date_on_market_field' )->nullable();
			$table->timestamp( 'date_price_reduced_field' )->nullable();
			$table->string( 'virtual_tour_url_field', 200 )->nullable();
			$table->boolean( 'show_on_3rd_party_sites_field' )->default(false)->nullable();
			$table->boolean( 'prices_starting_from_field' )->default(false)->nullable();
			$table->string( 'hot_property_title_field', 200 )->nullable();
			$table->decimal( 'area_field' )->nullable();
			$table->bigInteger( 'weight_field' )->nullable();
			$table->string( 'consultant_id', 10 )->index()->nullable();
			$table->foreign('consultant_id')->references('id')->on('consultants')->onDelete('set null');
			$table->float( 'latitude_field' )->nullable();
			$table->float( 'longitude_field' )->nullable();
			$table->boolean('show_in_searches')->default(true)->nullable();
			$table->boolean('is_managed_property')->default(false)->nullable();
			$table->string('three_d_walk_through')->nullable();
			$table->timestamp( 'date_off_market_field' )->nullable();
			$table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
			$table->bigInteger('project_id')->unsigned()->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->string('status', 50)->nullable();
			$table->integer('region_field')->nullable();
			$table->softDeletes();
			$table->timestamp( 'orig_created_at' )->nullable();
			$table->decimal('external_area_field')->nullable();
            $table->decimal('internal_area_field')->nullable();
            $table->decimal('plot_area_field', 10, 2)->nullable();
			$table->boolean( 'to_synch' )->default(false);
			$table->longText( 'data' )->nullable(); 
			$table->json('meta_data')->nullable(); 
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('properties');
	}

}
