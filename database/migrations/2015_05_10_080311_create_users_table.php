<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();  
            $table->bigInteger('user_type_id')->unsigned()->nullable(false);
            $table->foreign('user_type_id')->references('id')->on('user_types')->onUpdate('cascade');
            $table->string('username', 100)->unique()->nullable(true);
            $table->string('password', 100)->nullable(true);
            $table->string('provider_id', 100)->unique()->nullable(true);
            $table->string('provider', 20)->nullable(true);
            $table->timestamp('last_login')->nullable(true);
            $table->timestamp('email_verified_at')->nullable(true);
            $table->tinyInteger('active')->default(0)->nullable(true);
            $table->tinyInteger('send_updates')->default(false);
            $table->timestamp('password_last_update')->useCurrent();
            $table->timestamp('deleted_at')->nullable(true);
            $table->tinyInteger('notify_on_property_changes')->default(true);
            $table->tinyInteger('notify_on_property_sold')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
