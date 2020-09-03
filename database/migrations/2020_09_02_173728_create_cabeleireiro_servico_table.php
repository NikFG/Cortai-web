<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCabeleireiroServicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cabeleireiro_servico', function (Blueprint $table) {
            $table->unsignedBigInteger('cabeleireiro_id');
            $table->unsignedBigInteger('servico_id');
            
        });
        Schema::table('cabeleireiro_servico', function (Blueprint $table) {
            $table->primary(['cabeleireiro_id', 'servico_id']);
            $table->foreign('cabeleireiro_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('servico_id')->references('id')->on('servicos')
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
        Schema::dropIfExists('cabeleireiro_servico');
    }
}
