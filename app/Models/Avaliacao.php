<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model {
    protected $table = 'avaliacoes';

    public function horario() {
        return $this->hasOne('App\Models\Horario','horario_id');
    }
}
