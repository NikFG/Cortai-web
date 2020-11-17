<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicoController extends Controller {

    public function index() {
        $user = Auth::user();
        $servicos = null;

        if ($user->is_dono_salao) {
            $servicos = Servico::where('salao_id', $user->salao_id)->get();
        } else {
            $servicos = Servico::whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            })->get();
        }
        return response()->json($servicos, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id) {

        $servico = Servico::where('id',$id)->with('users')->get();
        return response()->json($servico, 200);
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
