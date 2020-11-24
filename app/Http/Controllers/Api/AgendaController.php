<?php

namespace App\Http\Controllers\Api;

use App\Events\ContaConfirmar;
use App\Events\ControlaAgenda;
use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Http\Request;

class AgendaController extends Controller {

    public function index() {
        $horario = Horario::find(7);
        event(new ControlaAgenda($horario));
        return response()->json($horario);
    }

    public function teste() {
        $quantidade = Horario::where('confirmado', false)->count();
        event(new ContaConfirmar(22, $quantidade));
        return response()->json($quantidade);
    }


}
