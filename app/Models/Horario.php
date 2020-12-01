<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model {

    /**
     * @var mixed
     */

    protected $casts = [
        'data' => 'date:d/m/Y',
        'hora' => 'date:H:i'
    ];

    public function avaliacoes() {
        return $this->hasMany('App\Models\Avaliacao');
    }

    public function cabeleireiro() {
        return $this->belongsTo('App\Models\User');
    }

    public function cliente() {
        return $this->belongsTo('App\Models\User');
    }

    public function salao() {
        return $this->belongsTo('App\Models\Salao');
    }

    public function forma_pagamento() {
        return $this->belongsTo('App\Models\FormaPagamento');
    }

    public function servicos() {
        return $this->belongsToMany('App\Models\Servico',
            'horario_servicos', 'horario_id')
            ->withPivot('descricao', 'valor');
    }
}
