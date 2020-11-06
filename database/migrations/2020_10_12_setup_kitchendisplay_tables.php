<?php 

namespace Thoughtco\KitchenDisplay\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KitchendisplayTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('thoughtco_kitchendisplay'))
        {
            Schema::create('thoughtco_kitchendisplay', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('locations');
                $table->string('categories');
                $table->string('order_assigned');
                $table->string('order_status');
                $table->integer('order_types');
                $table->text('display');
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('thoughtco_kitchendisplay');
    }
}