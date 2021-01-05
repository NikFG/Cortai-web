<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ServicosObservacaoDefault extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('servicos', function (Blueprint $table) {
            $table->text('observacao')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('servicos', function (Blueprint $table) {
            $table->text('observacao')->nullable()->change();
        });
    }
}
