<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePacklinkEntityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'packlink_entity',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->text('data');

                for ($i = 1; $i <= 7; $i++) {
                    $table->string('index_' . $i)->nullable();
                    $table->index('index_' . $i);
                }
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packlink_entity');
    }
}
