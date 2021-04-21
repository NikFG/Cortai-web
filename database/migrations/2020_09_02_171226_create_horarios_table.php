<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHorariosTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->time('hora');
            $table->boolean('confirmado')->nullable()->default(false);
            $table->boolean('pago')->nullable()->default(false);
            $table->boolean('cancelado')->nullable()->default(false);
            $table->unsignedBigInteger('cabeleireiro_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('salao_id');
            $table->unsignedBigInteger('forma_pagamento_id');
            $table->unsignedBigInteger('avaliacao_id')->nullable();
            $table->timestamps();
        });
        Schema::table('horarios', function (Blueprint $table) {
            $table->foreign('cabeleireiro_id')->references('id')->on('users');
            $table->foreign('cliente_id')->references('id')->on('users');
            $table->foreign('salao_id')->references('id')->on('saloes');
            $table->foreign('forma_pagamento_id')->references('id')->on('forma_pagamentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('horarios');
    }
}
