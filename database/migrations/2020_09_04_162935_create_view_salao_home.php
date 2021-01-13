<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
                    from (((saloes left join horarios on (saloes.id = horarios.salao_id)) left join servicos on (servicos.salao_id = saloes.id))
                             left join avaliacoes on (horarios.id = avaliacoes.horario_id))
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
