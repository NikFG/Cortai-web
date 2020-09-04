<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salao;
use App\Models\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SalaoController extends Controller {


    public function index(Request $request) {

        $dados = $request->only(['latitude', 'longitude', 'cidade']);
        try {
            Validator::make($dados, [
                'latitude' => 'required',
                'longitude' => 'required',
                'cidade' => 'required|string',
            ])->validate();

            $saloes = Salao::where('cidade', $dados['cidade'])
                ->get();

            $json = collect();
            foreach ($saloes as $salao) {
                $distancia = Util::haversineGreatCircleDistance($dados['latitude'],
                    $dados['longitude'], $salao->latitude, $salao->longitude);

                $json->push(['data' => $salao, 'distancia' => $distancia]);
            }
            return response()->json($json->sortBy(['distancia', 'nome']), 200);
        } catch (ValidationException $e) {
            return response()->json(['deu erro ao validar' => $e], 401);
        }

    }


    public function store(Request $request) {

        try {
            Validator::make($request->all(), [
                'nome' => 'required|string|max:70',
                'cidade' => 'required|string|max:150',
                'endereco' => 'required|string',
                'imagem' => 'string',//mudar pra img
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'telefone' => 'required|string|max:12',
            ])->validate();
        } catch (ValidationException $e) {
        }

        $salao = new Salao();
        $salao->nome = $request->nome;
        $salao->cidade = $request->cidade;
        $salao->endereco = $request->endereco;
        $salao->imagem = $request->imagem;
        $salao->latitude = $request->latitude;
        $salao->longitude = $request->longitude;
        $salao->telefone = $request->telefone;
        if ($salao->save()) {
            return response()->json(['Ok'], 200);
        }
        return response()->json(['Erro'], 400);
    }


    public function show($id) {
        $salao = Salao::findOrFail($id);
        return response()->json(['data' => $salao]);
    }


    public function update(Request $request, $id) {
        try {
            Validator::make($request->all(), [
                'nome' => 'required|string|max:70',
                'cidade' => 'required|string|max:150',
                'endereco' => 'required|string',
                'imagem' => 'string',//mudar pra img
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'telefone' => 'required|string|max:12',
            ])->validate();
        } catch (ValidationException $e) {
        }
        $salao = Salao::findOrFail($id);
        $salao->nome = $request->nome;
        $salao->cidade = $request->cidade;
        $salao->endereco = $request->endereco;
        $salao->imagem = $request->imagem;
        $salao->latitude = $request->latitude;
        $salao->longitude = $request->longitude;
        $salao->telefone = $request->telefone;
        if ($salao->save()) {
            return response()->json(['Ok'], 200);
        }
        return response()->json(['Erro'], 400);
    }


    public function destroy($id) {
        $salao = Salao::findOrFail($id);
        if ($salao->delete()) {
            return response()->json(['Ok'], 200);
        }
        return response()->json(['Erro'], 400);
    }

    public function teste(Request $request) {
        try {
            Validator::make($request->only(['latitude', 'longitude', 'cidade']), [
                'latitude' => 'required',
                'longitude' => 'required',
                'cidade' => 'required|string',
            ])->validate();
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $cidade = $request->cidade;
            $salao = DB::select("SELECT *,
                                        haversine(saloes_view.latitude,saloes_view.longitude,$latitude,$longitude)
                                        AS distancia FROM saloes_view where cidade = '$cidade' ORDER BY distancia,nome");
            return response()->json($salao, 200);
        } catch (ValidationException $e) {
            return response()->json($e, 400);
        }
    }
}
