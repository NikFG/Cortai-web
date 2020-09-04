<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaoFormapagamentosTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('salao_formapagamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('salao_id');
            $table->unsignedBigInteger('forma_pagamento_id');
            $table->string('descricao', 75);
            $table->float('valor');
            $table->timestamps();
        });
        Schema::table('salao_formapagamentos', function (Blueprint $table) {
            $table->primary(['salao_id', 'forma_pagamento_id']);
            $table->foreign('salao_id')->references('id')->on('saloes')
                ->onDelete('cascade');
            $table->foreign('forma_pagamento_id')->references('id')->on('forma_pagamentos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('salao_formapagamentos');
    }
}
