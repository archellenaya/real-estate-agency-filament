<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniqueLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unique_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 50)->unique()->nullable(false);
            $table->timestamp('date_expiry')->nullable(false);
            $table->timestamp('date_processed')->nullable(true);
            $table->bigInteger('link_type_id')->unsigned()->nullable(false);
            $table->foreign('link_type_id')->references('id')->on('unique_link_types')->onUpdate('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable(false);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('unique_links');
    }
}
