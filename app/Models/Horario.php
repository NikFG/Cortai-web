<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model {

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
    public function servicos() {
        return $this->belongsToMany('App\Models\Servico',
            'horario_servicos', 'horario_id')
            ->withPivot('descricao', 'valor');
    }
}
