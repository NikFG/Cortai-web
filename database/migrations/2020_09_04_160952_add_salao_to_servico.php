<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaoToServico extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('servicos', function (Blueprint $table) {
            $table->unsignedBigInteger('salao_id');
            $table->foreign('salao_id')->references('id')->on('saloes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropColumn('salao_id');
        });
    }
}
