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
        DB::unprepared("CREATE FUNCTION haversine (lat1 DOUBLE, lng1 DOUBLE, lat2 DOUBLE, lng2 DOUBLE) RETURNS DECIMAL(30,15)
                    BEGIN
                        DECLARE R INT;
                        DECLARE dLat DECIMAL(30,15);
                        DECLARE dLng DECIMAL(30,15);
                        DECLARE a1 DECIMAL(30,15);
                        DECLARE a2 DECIMAL(30,15);
                        DECLARE a DECIMAL(30,15);
                        DECLARE c DECIMAL(30,15);
                        DECLARE d DECIMAL(30,15);

                        SET R = 6371; -- Earth's radius in miles
                        SET dLat = RADIANS( lat2 ) - RADIANS( lat1 );
                        SET dLng = RADIANS( lng2 ) - RADIANS( lng1 );
                        SET a1 = SIN( dLat / 2 ) * SIN( dLat / 2 );
                        SET a2 = SIN( dLng / 2 ) * SIN( dLng / 2 ) * COS( RADIANS( lng1 )) * COS( RADIANS( lat2 ) );
                        SET a = a1 + a2;
                        SET c = 2 * ATAN2( SQRT( a ), SQRT( 1 - a ) );
                        SET d = R * c;
                        RETURN d;
                    END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement("DROP FUNCTION IF EXISTS haversine");
    }
}
