<?php

use Illuminate\Database\Migrations\Migration;

class CreateViewSalaoHome extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement("create or replace view saloes_view as
            select `cortai`.`saloes`.`id`                        AS `id`,
               `cortai`.`saloes`.`nome`                      AS `nome`,
               `cortai`.`saloes`.`cidade`                    AS `cidade`,
               `cortai`.`saloes`.`endereco`                  AS `endereco`,
               `cortai`.`saloes`.`imagem`                    AS `imagem`,
               `cortai`.`saloes`.`latitude`                  AS `latitude`,
               `cortai`.`saloes`.`longitude`                 AS `longitude`,
               `cortai`.`saloes`.`telefone`                  AS `telefone`,
               `cortai`.`saloes`.`created_at`                AS `created_at`,
               `cortai`.`saloes`.`updated_at`                AS `updated_at`,
               count(`cortai`.`avaliacoes`.`id`)             AS `qtd_avaliacao`,
               ifnull(avg(`cortai`.`avaliacoes`.`valor`), 0) AS `media`,
               ifnull(min(`cortai`.`servicos`.`valor`), 0)   AS `menor_valor`,
               ifnull(max(`cortai`.`servicos`.`valor`), 0)   AS `maior_valor`
            from `cortai`.`saloes`
                 left join `cortai`.`horarios` on ((`cortai`.`saloes`.`id` = `cortai`.`horarios`.`salao_id`))
                 left join `cortai`.`servicos` on ((`cortai`.`servicos`.`salao_id` = `cortai`.`saloes`.`id`))
                 left join `cortai`.`avaliacoes` on ((`cortai`.`horarios`.`id` = `cortai`.`avaliacoes`.`horario_id`))
                 inner join `cortai`.`funcionamentos` on ((`cortai`.`funcionamentos`.`salao_id` = `cortai`.`saloes`.`id`))
            where ((`cortai`.`servicos`.`deleted_at` is null) and (`cortai`.`saloes`.`deleted_at` is null))
            group by `cortai`.`saloes`.`id`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement("DROP VIEW saloes_view");
    }
}
