<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionamento extends Model {

    public function salao() {
        return $this->belongsTo('App\Models\Salao');
    }

}
