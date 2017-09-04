<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('attribute_id');
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('attribute_id')
                  ->references('id')->on('user_attributes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_values', function (Blueprint $table) {
            $table->dropForeign('user_values_attribute_id_foreign');
        });

        Schema::dropIfExists('user_values');
    }
}
