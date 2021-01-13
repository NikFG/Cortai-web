<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Funcionamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class FuncionamentoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index($id) {
        $funcionamento = Funcionamento::where('salao_id', $id)->get();
        return response()->json($funcionamento, 200);
    }

    public function indexDiaSemana($dia_semana, $salao_id) {
        $funcionamento = Funcionamento::where('salao_id', $salao_id)
            ->where('dia_semana', $dia_semana)->firstOrFail();
        return response()->json($funcionamento, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request) {
        $user = Auth::user();
        if ($user->is_dono_salao) {
            $validator = Validator::make($request->all(), [
                'dia_semana' => 'required|string|max:3',
                'horario_abertura' => 'required|date_format:H:i',
                'horario_fechamento' => 'required|date_format:H:i',
                'intervalo' => 'required|numeric',
                'salao_id' => 'required',
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 422);
            $funcionamento = Funcionamento::where('dia_semana', $request->dia_semana)
                ->where('salao_id', $request->salao_id)->first();
            if ($funcionamento == null) {
                $funcionamento = new Funcionamento();
            }

            $funcionamento->dia_semana = $request->dia_semana;
            $funcionamento->horario_abertura = $request->horario_abertura;
            $funcionamento->horario_fechamento = $request->horario_fechamento;
            $funcionamento->intervalo = $request->intervalo;
            $funcionamento->salao()->associate($request->salao_id);
            if ($funcionamento->save())
                return response()->json(['Ok']);
        }

        return response()->json(['Sem permissão'], 403);
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
     * @return JsonResponse
     */
    public function update(Request $request, $id) {
//        $id = Crypt::decrypt($id);
        $user = Auth::user();
        if ($user->is_dono_salao) {
            $validator = Validator::make($request->all(), [
                'dia_semana' => 'required|string|max:3',
                'horario_abertura' => 'required|date_format:H:i',
                'horario_fechamento' => 'required|date_format:H:i',
                'intervalo' => 'required|numeric',
                'salao_id' => 'required|exists:saloes,id',
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 422);
            $funcionamento = Funcionamento::findOrFail($id);
            $funcionamento->dia_semana = $request->dia_semana;
            $funcionamento->horario_abertura = $request->horario_abertura;
            $funcionamento->horario_fechamento = $request->horario_fechamento;
            $funcionamento->intervalo = $request->intervalo;
            $funcionamento->salao()->associate($request->salao_id);
            if ($funcionamento->save())
                return response()->json(['Ok']);
        }
        return response()->json(['Sem permissão'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id) {
        $user = Auth::user();
        if ($user->is_dono_salao) {
            $funcionamento = Funcionamento::findOrFail($id);
            if ($funcionamento->delete()) {
                return response()->json(['Ok']);
            }
        }
        return response()->json(['Sem permissão'], 403);
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
        return response()->json(['Sem permissão' => $user], 403);
    }
}
