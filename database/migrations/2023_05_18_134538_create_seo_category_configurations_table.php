<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeoCategoryConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seo_category_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('property_field', 250)->unique();
			$table->boolean('active')->nullable();
			$table->boolean('is_count')->nullable();
            $table->integer('sequence_no')->nullable();
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
        Schema::dropIfExists('seo_category_configurations');
    }
}
