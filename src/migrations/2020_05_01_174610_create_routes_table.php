<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('table_name')) {

            Schema::create('routes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('slug', 500)->index('slug')->nullable();
                $table->string('title', 250)->nullable();
                $table->string('description', 1200)->nullable();
                $table->string('keywords', 250)->nullable();
                $table->string('uses', 500)->index('uses')->nullable();
                $table->string('routable_type', 500)->index('routable_type')->nullable();
                $table->integer('routable_id')->nullable();
                $table->string('filters', 1750)->nullable();
                $table->tinyInteger('robots')->nullable()->default(0);
                $table->timestamps();

                $table->unique('slug', 'slug_2');

            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routes');
    }
}
