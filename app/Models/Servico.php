<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model {
    
    public function cabeleireiros() {
        return $this->belongsToMany('App\Models\User', 'cabeleireiro_servico', 'servico_id');
    }

    public function servicos() {
        return $this->belongsToMany('App\Models\Servico',
            'horario_servicos', 'servico_id')
            ->withPivot('descricao', 'valor');
    }
}
