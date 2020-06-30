<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->integer('ticket_id')->unsigned();
            $table->string('desc');
            $table->integer('by_person')->unsigned();
            $table->integer('created_by');
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('by_person')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropForeign(['by_person']);
        });
        Schema::dropIfExists('logs');
    }
}
