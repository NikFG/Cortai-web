<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salao extends Model {
    protected $table = 'saloes';

    public function avaliacoes() {
        return $this->hasMany('App\Models\Avaliacao');
    }

    public function horarios() {
        return $this->hasMany('App\Models\Horario');
    }

    public function funcionamentos() {
        return $this->hasMany('App\Models\Funcionamento');
    }

    public function cabeleireiros() {
        return $this->hasMany('App\Models\User');
    }

    public function forma_pagamentos() {
        return $this->belongsToMany('App\Models\FormaPagamento',
            'salao_formapagamentos', 'salao_id')
            ->withPivot('descricao', 'valor');
    }
}
