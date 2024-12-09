<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->increments('id');
            $table->text('field_name')->nullable();
            $table->string('field_key')->nullable();
            $table->string('field_group')->nullable();
            $table->string('field_group_name')->nullable();
            $table->string('sub_field_group')->nullable();
            $table->text('sub_field_group_name')->nullable();
            $table->tinyInteger('main_field')->nullable();
            $table->integer('form_type_id')->unsigned();
            $table->integer('active')->default('1')->nullable();
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
        Schema::dropIfExists('fields');
    }
}
