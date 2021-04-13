<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 75);
            $table->float('valor');
            $table->text('observacao')->default('')->nullable();
            $table->string('imagem', 300)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('salao_id');

        });
        Schema::table('servicos', function (Blueprint $table) {
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
        Schema::dropIfExists('servicos');
    }
}
