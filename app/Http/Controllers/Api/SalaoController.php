<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salao;
use App\Models\User;
use App\Models\Util;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SalaoController extends Controller {


    private $base_storage = 'images/salao/';

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

    public function cabeleireiros() {
        $user = Auth::user();
        $salao = Salao::findOrFail($user->salao_id);
        return response()->json($salao->cabeleireiros, 200);

    }

    public function store(Request $request) {
        $user = Auth::user();
        if ($user->is_dono_salao) {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:70',
                'cidade' => 'required|string|max:150',
                'endereco' => 'required|string',
                'imagem' => 'nullable|file',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'telefone' => 'required|celular_com_ddd|max:20',
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 422);
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
                    $file_name = $this->base_storage . $salao->id . '/' . 'perfil.' . $file->getClientOriginalExtension();
                    Storage::cloud()->put($file_name, file_get_contents($file));
                    $salao->imagem = $file_name;
                    $salao->save();
                }
                return response()->json($salao->id);
            }
        }
        return response()->json('Você não possui permissões suficientes', 403);
    }


    public function show($id) {
        $salao = Salao::findOrFail($id);
        if ($salao->imagem != null)
            try {
                $salao->imagem = base64_encode(Storage::cloud()->get($salao->imagem));
            } catch (FileNotFoundException $e) {
                return response()->json(['Arquivo não encontrado'], 500);
            }
        return response()->json($salao);
    }

    public function update(Request $request, $id) {


        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:70',
            'cidade' => 'required|string|max:150',
            'endereco' => 'required|string',
            'imagem' => 'nullable|file',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'telefone' => 'required|celular_com_ddd|max:20',
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $user = Auth::user();

        if ($user->is_dono_salao == true && $user->salao_id == $id) {


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
                $file_name = $this->base_storage . $salao->id . '/' . 'perfil.' . $file->getClientOriginalExtension();
                Storage::cloud()->put($file_name, file_get_contents($file));

                $salao->imagem = $file_name;
            }
            if ($salao->save()) {
                return response()->json(['Ok']);
            }
        }
        return response()->json(['Erro'], 500);
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
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'cidade' => 'required|string',
            ])->validate();
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $cidade = $request->cidade;
            $salao = DB::select(
                "SELECT *,
                      haversine(saloes_view.latitude,saloes_view.longitude,$latitude,$longitude)
                      AS distancia FROM saloes_view where cidade = '$cidade' ORDER BY distancia,nome");
            foreach ($salao as $s) {
                if ($s->imagem != null)
                    try {
                        $s->imagem = base64_encode(Storage::cloud()->get($s->imagem));
                    } catch (FileNotFoundException $e) {
                        return response()->json(['Arquivo não encontrado'], 500);
                    }
            }
            if (empty($salao)) {
                return response()->json([], 204);
            }
            return response()->json($salao);
        } catch (ValidationException $e) {
            return response()->json($e, 406);
        }
    }

    public function restore($id) {
        $salao = Salao::onlyTrashed()->findOrFail($id);

        if ($salao->restore()) {
            return response()->json(['Ok'], 200);
        }
        return response()->json(['Erro'], 400);
    }

    public function adicionaCabeleireiro(Request $request, $email) {
        $user = Auth::user();
        if ($user->is_dono_salao == true) {
            $cabeleireiro = User::where('email', $email)->firstOrFail();
            $cabeleireiro->salao()->associate($user->salao_id);
            $cabeleireiro->is_cabeleireiro = true;
            if ($cabeleireiro->save()) {
                return response()->json(['Ok'], 200);
            }
        }
        return response()->json(['Erro'], 400);
    }
}
