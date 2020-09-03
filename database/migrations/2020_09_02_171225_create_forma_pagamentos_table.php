<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormaPagamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forma_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 50);
            $table->string('imagem', 300)->nullable();
            $table->unsignedBigInteger('salao_id');
            $table->timestamps();
        });
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->foreign('salao_id')->references('id')->on('saloes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forma_pagamentos');
    }
}
