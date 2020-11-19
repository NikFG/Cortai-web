<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HorarioController extends Controller {


    public function clienteIndex() {
        $user = Auth::user();
        $horarios = Horario::where('cliente_id', $user->id)->get();
        return response()->json(compact('horarios'), 200);
    }

    public function cabeleireiroIndex() {
        $user = Auth::user();
        if ($user->is_cabeleireiro) {
            $horarios = Horario::where('cabeleireiro_id', $user->id)->get();
            return response()->json(compact('horarios'), 200);
        }
        return response()->json(['Erro'], 400);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
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
        $horario = new Horario();
        $horario->data = $request->data;
        $horario->hora = $request->hora;
        $horario->cliente_id = $request->data;
        $horario->cabeleireiro_id = $request->cabeleireiro_id;
        $horario->forma_pagamento_id = $request->forma_pagamento_id;
        $horario->cliente_id = $user->id;
        $horario->salao_id = User::select(['salao_id'])->find($request->cabeleireiro_id);
        if ($horario->save())
            return response()->json(['Ok'], 200);
        return response()->json(['Erro'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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
            if ($horario->save())
                return response()->json(['Ok'], 200);
        }
        return response()->json(['Erro'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id) {
        $horario = Horario::findOrFail($id);
        $horario->cancelado = true;
        $horario->save();
        return response()->json(['Ok'], 200);
    }
}
