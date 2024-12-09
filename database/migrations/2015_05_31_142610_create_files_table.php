<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\Components\FileStatus;

class CreateFilesTable extends Migration {
	private $enum_fileType = [
		'MainImage',
		'Plan',
		'OtherImages',
		'Brochure', 
		'Video'
	];
 
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'files', function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'property_id', 50 )->index();
			$table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
			$table->string( 'original_file_name' )->nullable();
			$table->string( 'file_name_field' )->nullable();
			$table->enum( 'file_type_field', $this->enum_fileType )->nullable();
			$table->integer( 'sequence_no_field' )->nullable();
			$table->string( 'mime' )->nullable();
			$table->string( 'seo_url_field' )->nullable();
			$table->string( 'orig_image_src', 255 )->nullable();
			$table->string('url_field', 255)->nullable();
			$table->integer('optimization_retries')->nullable();
			$table->string('image_status_field', 50)->default(FileStatus::TO_OPTIMIZE);
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop( 'files' );
	}
}
