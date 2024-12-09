<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('email_template_id')->unsigned()->nullable(false);
            $table->foreign('email_template_id')->references('id')->on('email_templates')->onUpdate('cascade');
            $table->string('subject', 100)->nullable(false);
            $table->longText('body')->nullable(false);
            $table->longText('attachments')->nullable(true);
            $table->string('recipient', 255)->nullable(false);
            $table->tinyInteger('sent')->nullable(false);
            $table->timestamp('date_sent')->nullable(true);
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
        Schema::dropIfExists('email_notifications');
    }
}
