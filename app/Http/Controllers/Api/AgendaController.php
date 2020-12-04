<?php

namespace App\Http\Controllers\Api;

use App\Events\AgendaCabeleireiro;
use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Support\Facades\Auth;
use OneSignal;

class AgendaController extends Controller {


    public function teste() {
        $horario = Horario::all();

//        $u = Auth::user();
//        event(new AgendaCabeleireiro(22, '2020-12-01'));
//
//        OneSignal::sendNotificationToAll(
//            'Texto',
//            $url = null,
//            $data = null,
//            $buttons = null,
//            $schedule = null,
//            "Tíulo customizado",
//        );
        $params = [];
        $params['android_accent_color'] = 'FFF57D21'; // argb color value
        $params['small_icon'] = 'ic_teste'; // icon res name specified in your app
        $params['large_icon'] = 'ic_teste'; // icon res name specified in your app
        OneSignal::addParams($params)->sendNotificationToExternalUser(
            "Some Message",
            "22",
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null,
            'Teste'
        );
        return response()->json(['Enviou']);
    }


}
