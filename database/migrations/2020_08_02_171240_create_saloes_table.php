<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaloesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saloes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 70);
            $table->string('cidade', 150);
            $table->text('endereco');
            $table->string('imagem', 300)->nullable();
            $table->double('latitude');
            $table->double('longitude');
            $table->string('telefone', 12);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saloes');
    }
}
