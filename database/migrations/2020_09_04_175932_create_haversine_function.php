<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHaversineFunction extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::unprepared("CREATE OR REPLACE FUNCTION haversine(latitude1 numeric(10,6),longitude1 numeric(10,6), latitude2 numeric(10,6), longitude2 numeric(10,6))
RETURNS double precision AS
$BODY$
	SELECT 6371 * acos( cos( radians(latitude1) ) * cos( radians( latitude2 ) ) * cos( radians( longitude1 ) - radians(longitude2) ) + sin( radians(latitude1) ) * sin( radians( latitude2 ) ) ) AS distance
$BODY$
LANGUAGE sql;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
      DB::statement("DROP FUNCTION IF EXISTS `haversine`");
    }
}
