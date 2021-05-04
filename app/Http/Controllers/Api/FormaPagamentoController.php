<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormaPagamento;
use App\Models\Salao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormaPagamentoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexSalao($salao_id) {
        $forma_pagamento = FormaPagamento::whereHas('saloes',
            function (Builder $query) use ($salao_id) {
                $query->where('salao_id', $salao_id);
            })->get();
        return response()->json($forma_pagamento, 200);
    }

    public function index() {
        $forma_pagamento = FormaPagamento::all();
        return response()->json($forma_pagamento, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $user = Auth::user();
        $lista = $request->only("pagamentos");
        $lista['pagamentos'] = str_replace(array('[', ']', '"'), '', $lista['pagamentos']);
        $lista = explode(',', $lista['pagamentos']);

        $explode_id = array_map('intval', $lista);
        $salao = Salao::find($user->salao_id);

        $salao->forma_pagamentos()->sync($explode_id);
        return response()->json("ok", 201);

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
