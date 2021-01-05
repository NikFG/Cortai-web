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
        DB::statemnt("CREATE VIEW saloes_view AS 
                    SELECT 
                        saloes.id AS id,
                        saloes.nome AS nome,
                        saloes.cidade AS cidade,
                        saloes.endereco AS endereco,
                        saloes.imagem AS imagem,
                        saloes.latitude AS latitude,
                        saloes.longitude AS longitude,
                        saloes.telefone AS telefone,
                        saloes.created_at AS created_at,
                        saloes.updated_at AS updated_at,
                        COUNT(avaliacoes.id) AS qtd_avaliacao,
                        AVG(avaliacoes.valor) AS media,
                        MIN(servicos.valor) AS menor_valor,
                        MAX(servicos.valor) AS maior_valor
                    FROM
                        (((saloes
                        INNER JOIN horarios ON (saloes.id = horarios.salao_id))
                        INNER JOIN servicos ON (servicos.salao_id = saloes.id))
                        INNER JOIN avaliacoes ON (horarios.id = avaliacoes.horario_id))
                    WHERE
                        servicos.ativo = true
                    GROUP BY saloes.id");
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
