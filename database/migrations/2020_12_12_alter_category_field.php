<?php 

namespace Thoughtco\KitchenDisplay\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCategoryField extends Migration
{
    public function up()
    {
        if (Schema::hasTable('thoughtco_kitchendisplay'))
        {
            Schema::table('thoughtco_kitchendisplay', function (Blueprint $table) {
                $table->text('categories')->change();
            });
        }
    }
}