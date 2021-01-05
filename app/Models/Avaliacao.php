<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model {
    protected $table = 'avaliacoes';

    public function horario() {
        return $this->belongsTo('App\Models\Horario');
    }
}
