<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salao extends Model {
    use SoftDeletes;

    protected $table = 'saloes';


    public function horarios() {
        return $this->hasMany('App\Models\Horario');
    }

    public function funcionamentos() {
        return $this->hasMany('App\Models\Funcionamento');
    }

    public function cabeleireiros() {
        return $this->hasMany('App\Models\User');
    }

    public function servicos() {
        return $this->belongsTo('App\Models\Servico');
    }
    public function forma_pagamentos() {
        return $this->belongsToMany('App\Models\FormaPagamento',
            'salao_formapagamentos', 'salao_id')
            ->withPivot('descricao', 'valor');
    }
}
