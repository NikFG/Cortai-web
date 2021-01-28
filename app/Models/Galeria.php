<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeria extends Model {
//    use HasFactory;


    public function servico() {
        return $this->belongsTo('App\Models\Servico');
    }
    public function cabeleireiro() {
        return $this->belongsTo('App\Models\User');
    }
    public function cliente() {
        return $this->belongsTo('App\Models\User');
    }
    public function salao(){
        return $this->belongsTo('App\Models\Salao');
    }
}
