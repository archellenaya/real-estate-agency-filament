<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('notification_trigger_id')->unsigned()->nullable(false);
            $table->foreign('notification_trigger_id')->references('id')->on('notification_triggers')->onUpdate('cascade');
            $table->string('subject', 100)->nullable(false);
            $table->longText('attachments')->nullable(true);
            $table->longText('body')->nullable(false);
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
        Schema::dropIfExists('email_templates');
    }
}
