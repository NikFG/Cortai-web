<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avaliacao;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AvaliacaoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index($id) {
        $avaliacoes = Avaliacao::with('horario')
            ->whereHas('horario', function (Builder $query) use ($id) {
                $query->where('salao_id', $id);
            })
            ->orderByDesc('data')
            ->get();
        return response()->json($avaliacoes, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request) {
        $user = Auth::user();


        $validator = Validator::make($request->all(), [
            'valor' => 'required|numeric',
            'data' => 'required|date',
            'observacao' => '',
            'horario_id' => 'required|exists:horarios',

        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $avaliacao = new Avaliacao();
        $avaliacao->data = Carbon::parse($request->data)->format('Y-m-d');
        $avaliacao->valor = $request->valor;
        $avaliacao->observacao = $request->observacao;
        $avaliacao->horario()->associate($request->horario_id);
        if ($avaliacao->save()) {
            return response()->json(['Ok'], 200);
        }
        return response()->json(['Erro'], 500);
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }
}
