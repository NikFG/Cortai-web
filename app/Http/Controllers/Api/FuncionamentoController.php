<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Funcionamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class FuncionamentoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($id) {
        $funcionamento = Funcionamento::where('salao_id', $id)->get();
        return response()->json($funcionamento, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $user = Auth::user();
        if ($user->is_dono_salao)
            try {
                Validator::make($request->all(), [
                    'dia_semana' => 'required|string|max:3',
                    'horario_abertura' => 'required',
                    'horario_fechamento' => 'required',
                    'intervalo' => 'required|numeric',
                    'salao_id' => 'required',
                ])->validate();
                $funcionamento = Funcionamento::where('dia_semana', $request->dia_semana)
                    ->where('salao_id', $request->salao_id)->first();
                if ($funcionamento != null) {
                    $this->destroyAll();
                }
                $funcionamento = new Funcionamento();
                $funcionamento->dia_semana = $request->dia_semana;
                $funcionamento->horario_abertura = $request->horario_abertura;
                $funcionamento->horario_fechamento = $request->horario_fechamento;
                $funcionamento->intervalo = $request->intervalo;
                $funcionamento->salao()->associate($request->salao_id);
                if ($funcionamento->save())
                    return response()->json(['Ok']);
            } catch (ValidationException $e) {
                return response()->json($e, 500);
            }
        return response()->json(['Sem permissão'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
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
        if ($user->is_dono_salao)
            try {
                Validator::make($request->all(), [
                    'dia_semana' => 'required|string|max:3',
                    'horario_abertura' => 'required',
                    'horario_fechamento' => 'required',
                    'intervalo' => 'required|numeric',
                    'salao_id' => 'required',
                ])->validate();

                $funcionamento = Funcionamento::findOrFail($id);
                $funcionamento->dia_semana = $request->dia_semana;
                $funcionamento->horario_abertura = $request->horario_abertura;
                $funcionamento->horario_fechamento = $request->horario_fechamento;
                $funcionamento->intervalo = $request->intervalo;
                $funcionamento->salao()->associate($request->salao_id);
                if ($funcionamento->save())
                    return response()->json(['Ok']);
            } catch (ValidationException $e) {
                return response()->json($e, 500);
            }
        return response()->json(['Sem permissão'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id) {
        $user = Auth::user();
        if ($user->is_dono_salao) {
            $funcionamento = Funcionamento::findOrFail($id);
            if ($funcionamento->delete()) {
                return response()->json(['Ok']);
            }
        }
        return response()->json(['Sem permissão'], 500);
    }

    public function destroyAll() {
        $user = Auth::user();
        if ($user->is_dono_salao == 1) {
            $funcionamento = Funcionamento::where('salao_id', $user->salao_id)->get();
            foreach ($funcionamento as $f) {
                $f->delete();
            }
            return response()->json(['Ok']);

        }
        return response()->json(['Sem permissão' => $user], 500);
    }
}
