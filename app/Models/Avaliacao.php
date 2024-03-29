<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model {
    protected $table = 'avaliacoes';

    protected $casts = [
        'data' => 'date:d/m/Y',
    ];

    public function horario() {
        return $this->belongsTo('App\Models\Horario');
    }
}
