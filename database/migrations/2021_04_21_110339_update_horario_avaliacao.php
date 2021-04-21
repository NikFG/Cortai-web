<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHorarioTela extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('horarios', function (Blueprint $table) {
            $table->unsignedBigInteger('avaliacao_id');
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
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropForeign("avaliacao_id");
            $table->removeColumn('avaliacao_id');
        });
    }
}
