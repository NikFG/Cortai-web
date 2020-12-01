<?php

namespace App\Http\Controllers\Api;

use App\Events\AgendaCabeleireiro;
use App\Events\ContaConfirmar;
use App\Events\ControlaAgenda;
use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller {

    public function index() {
        $horario = Horario::find(7);
        event(new ControlaAgenda($horario));
        return response()->json($horario);
    }

    public function teste() {
        $horario = Horario::where('pago',false)->get();
        $u = Auth::user();
        event(new AgendaCabeleireiro($horario, 22));
        return response()->json($horario);
    }


}
