<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHorarioServicosTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('horario_servicos', function (Blueprint $table) {
            $table->unsignedBigInteger('horario_id');
            $table->unsignedBigInteger('servico_id');
            $table->string('descricao', 75);
            $table->float('valor');
            $table->timestamps();
        });
        Schema::table('horario_servicos', function (Blueprint $table) {
            $table->primary(['horario_id', 'servico_id']);
            $table->foreign('horario_id')->references('id')->on('horarios')
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
    public function down() {
        Schema::dropIfExists('horario_servicos');
    }
}
