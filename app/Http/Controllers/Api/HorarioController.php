<?php

namespace App\Http\Controllers\Api;

use App\Events\AgendaCabeleireiro;
use App\Events\ContaConfirmar;
use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use OneSignal;

class HorarioController extends Controller {


    public function clienteIndex(Request $request) {
        $user = Auth::user();
        $horarios = Horario::where('cliente_id', $user->id)
            ->where('pago', $request->pago)
            ->where('cancelado', false)
            ->orderBy('data', 'desc')
            ->orderBy('hora', 'desc')
            ->with('servicos')->has('servicos')
            ->with('cliente')
            ->with('cabeleireiro')
            ->get();
        if ($horarios->count() == 0) {
            return response()->json([0 => "Não há horários"], 404);
        }
        return response()->json(compact('horarios'), 200);
    }

    public function cabeleireiroIndex() {
        $user = Auth::user();
        if ($user->is_cabeleireiro) {
            $horarios = Horario::where('cabeleireiro_id', $user->id)
                ->where('cancelado', false)
                ->with('cliente')
                ->with('cabeleireiro')
                ->with('servicos')
                ->orderBy('data', 'desc')
                ->orderBy('hora', 'desc')
                ->get();
            if ($horarios->count() == 0) {
                return response()->json('Não  há horários', 204);
            }
            return response()->json($horarios);
        }
        return response()->json(['Erro'], 400);
    }

    public function calendario() {
        $user = Auth::user();
        if ($user->is_cabeleireiro) {
            $dia_hoje = Carbon::today();
            $horarios = Horario::where('cabeleireiro_id', $user->id)
                ->where('confirmado', false)
                ->where('cancelado', false)
                ->whereDate('data', '>=', $dia_hoje)
                ->with('cliente')
                ->with('cabeleireiro')
                ->with('servicos')
                ->orderBy('data', 'desc')
                ->orderBy('hora', 'desc')
                ->get();
            if ($horarios->count() == 0) {
                return response()->json('Não  há horários', 404);
            }
            $horarios_hoje = $horarios->where('data', $dia_hoje);
            $horarios_sete = $horarios->whereBetween('data', [Carbon::today()->addDay(), Carbon::today()->addDays(7)]);
            $horarios_mes = $horarios->where('data', '>', Carbon::today()->addDays(7));
            return response()->json([$horarios_hoje, $horarios_sete, $horarios_mes]);
        }
        return response()->json(['Erro'], 400);
    }

    public function agenda($cabeleireiro_id, $data) {
        $formatada = Carbon::parse($data);
        $user = Auth::user();
        $horarios = Horario::where('cabeleireiro_id', $cabeleireiro_id)
            ->with('cliente')
            ->with('cabeleireiro')
            ->where('data', $formatada->format('Y-m-d'))
            ->get();
        return response()->json($horarios, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request) {
        $user = Auth::user();


        $validator = Validator::make($request->all(), [
            'cabeleireiro_id' => 'required|exists:users,id',
            'cliente_id' => 'required|exists:users,id',
            'confirmado' => 'required',
            'data' => 'required|date',
            'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
            'hora' => 'required|date_format:H:i',
            'pago' => 'required',
            'servicos' => 'required|array'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $horario = new Horario();
        $horario->cabeleireiro()->associate($request->cabeleireiro_id);
        $horario->cliente()->associate($request->cliente_id);
        $salao_id = (User::select(['salao_id'])->find($request->cabeleireiro_id))->salao_id;
        $horario->salao()->associate($salao_id);
        $horario->forma_pagamento()->associate($request->forma_pagamento_id);
        $horario->confirmado = $request->confirmado;
        $horario->data = Carbon::parse($request->data)->format('Y-m-d');
        $horario->hora = $request->hora;
        $horario->pago = $request->pago;
        if ($horario->save()) {
            foreach ($request->servicos as $s) {
                $horario->servicos()->sync([$s['id'] => ['descricao' => $s['nome'], 'valor' => $s['valor']]]);
            }
            event(new AgendaCabeleireiro($horario->cabeleireiro_id, $horario->data));
            $params = [];
            $params['android_accent_color'] = 'FFF57D21'; // argb color value
            $params['small_icon'] = 'ic_noti_icon'; // icon res name specified in your app
            $params['large_icon'] = 'ic_noti_icon'; // icon res name specified in your app
            OneSignal::addParams($params)->sendNotificationToExternalUser(
                "Dia {$request->data} às {$request->hora}.Verifique seu app!!",
                $horario->cabeleireiro->id,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null,
                "Novo horário marcado com você {$horario->cabeleireiro->nome}!"
            );

            event(new ContaConfirmar($horario->cabeleireiro_id, $this->contaHorario($horario->cabeleireiro_id)));

            return response()->json(['Ok'], 200);
        }

        return response()->json(['Erro'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id) {
        $user = Auth::user();
        $horario = Horario::findOrFail($id);
        if ($horario->cliente->id == $user->id || $horario->cabeleireiro->id == $user->id) {
            return response()->json($horario, 200);
        }
        return response()->json(['Erro'], 500);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id) {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'cabeleireiro_id' => 'required|exists:users,id',
            'data' => 'required|date',
            'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
            'hora' => 'required|date_format:H:i',
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $horario = Horario::findOrFail($id);
        if ($horario->cliente->id == $user->id || $horario->cabeleireiro->id == $user->id) {
            $horario->data = $request->data;
            $horario->hora = $request->hora;
            $horario->cliente_id = $request->data;
            $horario->forma_pagamento_id = $request->forma_pagamento_id;
            if ($horario->save()) {
                event(new AgendaCabeleireiro($horario->cabeleireiro_id, $horario->data));
                return response()->json(['Ok'], 200);
            }
        }
        return response()->json(['Erro'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id) {
        $horario = Horario::findOrFail($id);
        $horario->cancelado = true;
        $horario->save();
        return response()->json(['Ok'], 200);
    }

    public function confirmaHorario($id) {
        $horario = Horario::findOrFail($id);
        $horario->confirmado = true;
        $horario->save();
        $quantidade = $this->contaHorario($horario->cabeleireiro_id);
        $params = [];
        $params['android_accent_color'] = 'FFF57D21'; // argb color value
        $params['small_icon'] = 'ic_noti_icon'; // icon res name specified in your app
        $params['large_icon'] = 'ic_noti_icon'; // icon res name specified in your app
        $data = Carbon::parse($horario->data);
        $hora = Carbon::parse($horario->hora);
        OneSignal::addParams($params)->sendNotificationToExternalUser(
            "Dia {$data->format('d/m/Y')} às {$hora->format('H:i')}.Verifique seu app!!",
            $horario->cabeleireiro->id,
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null,
            "Horário confirmado com {$horario->cabeleireiro->nome}! =)"
        );
        event(new ContaConfirmar($horario->cabeleireiro_id, $quantidade));
        return response()->json(['Ok']);
    }

    public function cancelaHorario($id) {
        $horario = Horario::findOrFail($id);
        $horario->cancelado = true;
        $horario->save();
        $quantidade = $this->contaHorario($horario->cabeleireiro_id);
        $params = [];
        $params['android_accent_color'] = 'FFF57D21'; // argb color value
        $params['small_icon'] = 'ic_noti_icon'; // icon res name specified in your app
        $params['large_icon'] = 'ic_noti_icon'; // icon res name specified in your app
        $data = Carbon::parse($horario->data);
        $hora = Carbon::parse($horario->hora);
        OneSignal::addParams($params)->sendNotificationToExternalUser(
            "Dia {$data->format('d/m/Y')} às {$hora->format('H:i')}\n
            Pedimos desculpas pelo ocorrido, mas você pode agendar novamente",
            $horario->cabeleireiro->id,
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null,
            "Infelizmente seu horário com {$horario->cabeleireiro->nome} foi cancelado! =("
        );
        event(new ContaConfirmar($horario->cabeleireiro_id, $quantidade));
        return response()->json(['Ok']);
    }


    public function confirmaPagamento($id) {
        $horario = Horario::findOrFail($id);
        $horario->pago = true;
        $horario->save();
        return response()->json(['Ok']);
    }

    public function conta($id) {
        return response()->json(['quantidade' => $this->contaHorario($id)]);
    }

    private function contaHorario($id) {
        return Horario::where('confirmado', false)
            ->where('cancelado', false)
            ->where('cabeleireiro_id', $id)
            ->count();
    }
}
