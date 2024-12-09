<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDataImportsTable extends Migration
{
    public function up()
    {
        Schema::create('data_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->longText('payload')->nullable();
            $table->text('exception')->nullable();
            $table->text('status')->nullable(); // done, failed, 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_imports');
    }
}

