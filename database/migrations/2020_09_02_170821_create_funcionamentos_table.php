<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuncionamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funcionamentos', function (Blueprint $table) {
            $table->id();
            $table->enum('dia_semana', ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB']);
            $table->time('horario_abertura');
            $table->time('horario_fechamento');
            $table->integer('intervalo');
            $table->unsignedBigInteger('salao_id');
            $table->timestamps();
        });

        Schema::table('funcionamentos', function (Blueprint $table) {
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
        Schema::dropIfExists('funcionamentos');
    }
}
