<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandledTagsTables extends Migration
{
    public function up()
    {
        Schema::create('handled_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60);
            $table->string('slug', 80)->unique();
            $table->string('color', 7)->default('#5B6B7A');
            $table->timestamps();
        });

        Schema::create('handled_conversation_tag', function (Blueprint $table) {
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('applied_by_user_id')->nullable();
            $table->timestamps();

            $table->primary(['conversation_id', 'tag_id']);
            $table->index('tag_id');

            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('handled_tags')->onDelete('cascade');
            $table->foreign('applied_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('handled_conversation_tag');
        Schema::dropIfExists('handled_tags');
    }
}
