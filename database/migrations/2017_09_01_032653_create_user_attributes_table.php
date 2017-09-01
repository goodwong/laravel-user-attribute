<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('context', 32)->nullable();
            $table->unsignedInteger('group_id');
            $table->string('label', 32);
            $table->string('type', 32)->default('input.text')->comment('input.text | input.tel | textarea | radio | images | files | ...');
            $table->string('code', 32)->nullable()->comment("e.g. raw_address; 同一context下唯一");
            $table->integer('position')->default(0)->comment('越小越前');
            $table->jsonb('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_attributes');
    }
}
