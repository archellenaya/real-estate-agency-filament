<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\Components\FileStatus;

class CreateConsultantsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(){
		Schema::create('consultants', function (Blueprint $table){
			$table->string('id', 10)->unique(); 
			$table->string('agent_code')->nullable(); 
			$table->string('full_name_field')->nullable();
			$table->string('image_file_name_field')->nullable();
			$table->string('image_name_field')->nullable();
			$table->bigInteger('branch_id_field')->nullable();
			$table->text('description_field')->nullable();
			$table->string('designation_field', 50)->nullable();
			$table->string('contact_number_field', 50)->nullable();
			$table->string('whatsapp_number_field', 50)->nullable();
			$table->string('email_field', 300)->nullable();
			$table->boolean('is_available')->nullable();
			$table->string('old_id', 100)->unique();
			$table->string('office_phone_field', 50)->nullable();
			$table->string('orig_consultant_image_src')->nullable();
			$table->string('external_id')->nullable();
			$table->boolean( 'to_synch' )->default(false);
			$table->longText( 'data' )->nullable(); 
			$table->string('image_status_field', 50)->default(FileStatus::TO_OPTIMIZE);
			$table->string('url_field', 1024)->nullable();
			$table->integer('optimization_retries')->nullable();
			$table->timestamps();
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::drop('consultants');
	}
}
