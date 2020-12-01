<?php

namespace App\Http\Controllers\Api;

use App\Events\AgendaCabeleireiro;
use App\Events\ContaConfirmar;
use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HorarioController extends Controller {


    public function clienteIndex(Request $request) {
        $user = Auth::user();
        $horarios = Horario::where('cliente_id', $user->id)
            ->where('pago', $request->pago)
            ->orderBy('data', 'desc')
            ->orderBy('hora', 'desc')
            ->with('servicos')->has('servicos')
            ->with('cliente')
            ->with('cabeleireiro')
            ->get();
        return response()->json(compact('horarios'), 200);
    }

    public function cabeleireiroIndex($confirmado) {
        $user = Auth::user();
        if ($user->is_cabeleireiro) {
            $horarios = Horario::where('cabeleireiro_id', $user->id)
                ->where('confirmado', $confirmado)
                ->with('cliente')
                ->with('cabeleireiro')
                ->with('servicos')
                ->orderBy('data', 'desc')
                ->orderBy('hora', 'desc')
                ->get();
            return response()->json($horarios, 200);
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

        try {
            Validator::make($request->all(), [
                'cabeleireiro_id' => 'required',
                'cliente_id' => 'required',
                'confirmado' => 'required',
                'data' => 'required',
                'forma_pagamento_id' => 'required',
                'hora' => 'required',
                'pago' => 'required',
                'servicos' => 'required'
            ])->validate();
        } catch (ValidationException $e) {
            return response()->json($e, 500);
        }
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
            event(new ContaConfirmar($horario->cabeleireiro_id, $this->contaHorario($horario->cabeleireiro_id)));
            event(new AgendaCabeleireiro($horario->cabeleireiro_id, $horario->data));
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
        try {
            Validator::make($request->all(), [
                'data' => 'required|date',
                'hora' => 'required',
                'cabeleireiro_id' => 'required',
                'forma_pagamento_id' => 'required',
            ])->validate();
        } catch (ValidationException $e) {
            return response()->json($e, 500);
        }
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
        event(new ContaConfirmar($horario->cabeleireiro_id, $quantidade));
        return response()->json(['Ok']);
    }

    public function cancelaHorario($id) {
        $horario = Horario::findOrFail($id);
        $horario->cancelado = true;
        $horario->save();
        $quantidade = $this->contaHorario($horario->cabeleireiro_id);
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
