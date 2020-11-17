<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model {

    public function users() {
        return $this->belongsToMany('App\Models\User',
            'cabeleireiro_servico', 'servico_id','cabeleireiro_id');
    }
}
