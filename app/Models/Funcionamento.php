<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionamento extends Model {
    /**
     * @var mixed
     */
    protected $casts = [
        'horario_abertura' => 'date:H:i',
        'horario_fechamento' => 'date:H:i'
    ];


    public function salao() {
        return $this->belongsTo('App\Models\Salao');
    }

}
