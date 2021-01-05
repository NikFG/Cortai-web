<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Driver;

class FixHorarioColumnConfirmado extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('horarios', function (Blueprint $table) {
            $table->renameColumn('confirmando','confirmado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('horarios', function (Blueprint $table) {
            $table->renameColumn('confirmado','confirmando');
        });
    }
}
