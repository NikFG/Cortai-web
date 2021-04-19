<?php

use Illuminate\Database\Migrations\Migration;

class CreateViewSalaoHome extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement("CREATE OR REPLACE VIEW saloes_view AS
                    select saloes.id                        AS id,
                           saloes.nome                      AS nome,
                           saloes.cidade                    AS cidade,
                           saloes.endereco                  AS endereco,
                           saloes.imagem                    AS imagem,
                           saloes.latitude                  AS latitude,
                           saloes.longitude                 AS longitude,
                           saloes.telefone                  AS telefone,
                           saloes.created_at                AS created_at,
                           saloes.updated_at                AS updated_at,
                           count(avaliacoes.id)             AS qtd_avaliacao,
                           ifnull(avg(avaliacoes.valor), 0) AS media,
                           ifnull(min(servicos.valor), 0)   AS menor_valor,
                           ifnull(max(servicos.valor), 0)   AS maior_valor
                    from (((`cortai`.`saloes` left join `cortai`.`horarios` on (`cortai`.`saloes`.`id` = `cortai`.`horarios`.`salao_id`))
                        left join `cortai`.`servicos` on (`cortai`.`servicos`.`salao_id` = `cortai`.`saloes`.`id`))
                        left join `cortai`.`avaliacoes` on (`cortai`.`horarios`.`id` = `cortai`.`avaliacoes`.`horario_id`)
                        inner join funcionamentos on (`cortai`.`funcionamentos`.`salao_id` = saloes.id))
                    where servicos.deleted_at is null
                      and saloes.deleted_at is null
                    group by saloes.id;");
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
