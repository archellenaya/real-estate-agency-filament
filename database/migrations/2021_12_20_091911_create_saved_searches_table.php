<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->bigInteger('type_id')->unsigned()->nullable(true);
            // $table->foreign('type_id')->references('id')->on('types')->onUpdate('cascade');
            $table->string('name', 100)->nullable();
            // $table->string('property_type_id', 100)->nullable(true);
            // $table->string('location_id', 100)->nullable(true);
            // $table->double('min_price')->nullable(true);
            // $table->double('max_price')->nullable(true);
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->text('url');
            $table->tinyInteger('alerts')->unsigned();
            $table->bigInteger('email_frequency_id')->unsigned()->nullable();
            $table->foreign('email_frequency_id')->references('id')->on('email_frequencies')->onUpdate('cascade');
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
        Schema::dropIfExists('saved_searches');
    }
}
