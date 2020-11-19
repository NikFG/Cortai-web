<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servico extends Model {
    use SoftDeletes;

    public function cabeleireiros() {
        return $this->belongsToMany('App\Models\User',
            'cabeleireiro_servico', 'servico_id', 'cabeleireiro_id');
    }
    public function salao() {
        return $this->belongsTo('App\Models\Salao');
    }
}
