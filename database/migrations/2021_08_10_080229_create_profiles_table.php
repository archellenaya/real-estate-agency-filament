<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('prefix', 5)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->bigInteger('buyer_type_id')->unsigned()->nullable();
            $table->foreign('buyer_type_id')->references('id')->on('buyer_types')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('interest_id')->unsigned()->nullable();
            $table->foreign('interest_id')->references('id')->on('interests')->onDelete('cascade')->onUpdate('cascade');
            $table->string('currency', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('prefix_contact_number', 8)->nullable(true);
            $table->string('contact_number', 50)->nullable();
            $table->string('profile_image_filename')->nullable();
            $table->string('region', 100)->nullable();
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
        Schema::dropIfExists('profiles');
    }
}
