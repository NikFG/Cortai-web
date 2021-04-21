<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacaoesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->float('valor');
            $table->date('data');
            $table->text('observacao')->nullable();
            $table->unsignedBigInteger('horario_id');
            $table->timestamps();
        });
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->foreign('horario_id')->references('id')->on('horarios');
        });
        Schema::table('horarios', function (Blueprint $table) {
        $table->foreign('avaliacao_id')->references('id')->on('avaliacoes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('avaliacoes');
    }
}
