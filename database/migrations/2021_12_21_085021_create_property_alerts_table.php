<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',100);
            $table->bigInteger('type_id')->unsigned()->nullable(true);
            $table->foreign('type_id')->references('id')->on('types')->onUpdate('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable(true);
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
            $table->string('property_type_id', 1024)->nullable(true);
            $table->string('location_id', 1020)->nullable(true);
            $table->double('min_price')->nullable(true);
            $table->double('max_price')->nullable(true);
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
        Schema::dropIfExists('property_alerts');
    }
}
