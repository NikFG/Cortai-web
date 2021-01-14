<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model {
    /*
        public function salao() {
            return $this->belongsToMany('App\Models\Salao');
        }*/
    public function horarios() {
        return $this->hasMany('App\Models\Horario');
    }

    public function saloes() {
        return $this->belongsToMany('App\Models\Salao',
            'salao_formapagamentos', 'forma_pagamento_id','salao_id')
            ->withPivot('descricao', 'valor');
    }
}
