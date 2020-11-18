<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salao;
use App\Models\User;
use App\Models\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                'imagem' => '',
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
        $salao->latitude = $request->latitude;
        $salao->longitude = $request->longitude;
        $salao->telefone = $request->telefone;

        if ($salao->save()) {
            $user = Auth::user();
            $user->salao()->associate($salao->id);
            $user->is_dono_salao = true;
            $user->is_cabeleireiro = true;
            $user->save();
            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                if (!$file->isValid()) {
                    return response()->json(['invalid_file_upload'], 400);
                }
                $path = storage_path() . '/img/salao/' . $salao->id . '/';
                $file_name = 'perfil.' . $file->getClientOriginalExtension();
                $file->move($path, $file_name);
                $salao->imagem = 'storage/img/salao/' . $salao->id . '/' . $file_name;
                $salao->save();
            }
            return response()->json(["Ok"], 200);
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
                'imagem' => '',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'telefone' => 'required|string|max:12',
            ])->validate();
        } catch (ValidationException $e) {
            return response()->json($e, 500);
        }
        $user = Auth::user();

        if ($user->is_dono_salao==true && $user->salao_id == $id) {


            $salao = Salao::findOrFail($id);
            $salao->nome = $request->nome;
            $salao->cidade = $request->cidade;
            $salao->endereco = $request->endereco;

            $salao->latitude = $request->latitude;
            $salao->longitude = $request->longitude;
            $salao->telefone = $request->telefone;

            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                if (!$file->isValid()) {
                    return response()->json(['invalid_file_upload'], 400);
                }
                $path = storage_path() . '/img/salao/' . $salao->id . '/';
                $file_name = 'perfil.png';
                $file->move($path, $file_name);
                $salao->imagem = 'storage/img/salao/' . $salao->id . '/' . $file_name;
            }
            if ($salao->save()) {
                return response()->json(['Ok'], 200);
            }
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

    public function home(Request $request) {

        try {
            Validator::make($request->only(['latitude', 'longitude', 'cidade']), [
                'latitude' => 'required',
                'longitude' => 'required',
                'cidade' => 'required|string',
            ])->validate();
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $cidade = $request->cidade;
            $salao = DB::select(
                "SELECT *,
                      haversine(saloes_view.latitude,saloes_view.longitude,$latitude,$longitude)
                      AS distancia FROM saloes_view where cidade = '$cidade' ORDER BY distancia,nome");
            return response()->json($salao, 200);
        } catch (ValidationException $e) {
            return response()->json($e, 406);
        }
    }
}
