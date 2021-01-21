<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaleriasTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('galerias', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 200);
            $table->string('imagem', 200);
            $table->unsignedBigInteger('cabeleireiro_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('salao_id');
            $table->unsignedBigInteger('servico_id');
            $table->timestamps();
        });
        Schema::table('galerias', function (Blueprint $table) {
            $table->foreign('cabeleireiro_id')->references('id')->on('users');
            $table->foreign('cliente_id')->references('id')->on('users');
            $table->foreign('salao_id')->references('id')->on('saloes');
            $table->foreign('servico_id')->references('id')->on('servicos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('galerias');
    }
}
