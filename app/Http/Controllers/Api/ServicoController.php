<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Dotenv\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ServicoController extends Controller {

    public function index() {
        $user = Auth::user();
        $servicos = null;

        if ($user->is_dono_salao) {
            $servicos = Servico::where('salao_id', $user->salao_id)->get();
        } else {
            $servicos = Servico::whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            })->where('salao_id', $user->salao_id)->get();
        }
        return response()->json($servicos, 200);
    }

    public function servicoSalao($idSalao) {
        $user = Auth::user();
        $servicos = Servico::where('salao_id', $idSalao)
            ->has('cabeleireiros')
            ->with('cabeleireiros')
            ->orderBy('nome')
            ->get();

        return response()->json($servicos, 200);
    }

    public function indexAll() {
        $user = Auth::user();
        $servicos = null;

        if ($user->is_dono_salao) {
            $servicos = Servico::withTrashed()
                ->with('cabeleireiros')
                ->where('salao_id', $user->salao_id)
                ->orderBy('deleted_at')
                ->orderBy('nome')
                ->get();
        } else {
            $servicos = Servico::withTrashed()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('id', $user->id);
                })
                ->where('salao_id', $user->salao_id)
                ->orderBy('deleted_at')
                ->orderBy('nome')
                ->get();
        }
        return response()->json($servicos, 200);
    }

    public function indexDeleted() {
        $user = Auth::user();
        $servicos = null;

        if ($user->is_dono_salao) {
            $servicos = Servico::onlyTrashed()->where('salao_id', $user->salao_id)->get();
        } else {
            $servicos = Servico::onlyTrashed()->whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            })->where('salao_id', $user->salao_id)->get();
        }
        return response()->json($servicos, 200);
    }

    public function store(Request $request) {
        $user = Auth::user();
        if ($user->is_dono_salao || $user->is_cabeleireiro) {
            try {
                \Illuminate\Support\Facades\Validator::make($request->all(), [
                    'nome' => 'required|string|max:75',
                    'valor' => 'required|numeric',
                    'observacao' => '',
                    'cabeleireiros' => 'required',
                    'imagem' => '',
                ])->validate();
            } catch (ValidationException $e) {
                return response()->json($e, 500);
            }
            $servico = new Servico();
            $servico->nome = $request->nome;
            $servico->valor = $request->valor;
            $servico->observacao = $request->observacao;
            $servico->salao()->associate($user->salao_id);

            if ($servico->save()) {
                $servico->cabeleireiros()->sync($request->cabeleireiros);
                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    if (!$file->isValid()) {
                        return response()->json(['invalid_file_upload'], 400);
                    }
                    $path = storage_path() . '/img/servico/' . $servico->id . '/';
                    $file_name = 'perfil.png';
                    $file->move($path, $file_name);
                    $servico->imagem = 'storage/img/servico/' . $servico->id . '/' . $file_name;
                    $servico->save();
                }
            }

            return response()->json(['Ok'], 200);
        }
        return response()->json(["erro"], 500);

    }


    public function show(int $id) {

        $servico = Servico::where('id', $id)->with('users')->get();
        return response()->json($servico, 200);
    }


    public function update(Request $request, $id) {
        $user = Auth::user();
        if ($user->is_dono_salao || $user->is_cabeleireiro) {
            try {
                \Illuminate\Support\Facades\Validator::make($request->all(), [
                    'nome' => 'required|string|max:75',
                    'valor' => 'required|numeric',
                    'observacao' => 'string',
                    'imagem' => '',
                    'ativo' => 'required',
                ])->validate();
            } catch (ValidationException $e) {
                return response()->json($e, 500);
            }

            $servico = Servico::findOrFail($id);
            if ($this->permite_alterar_servico($user, $servico)) {
                $servico->nome = $request->nome;
                $servico->valor = $request->valor;
                $servico->observacao = $request->observacao;
                $servico->salao_id = $user->salao_id;
                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    if (!$file->isValid()) {
                        return response()->json(['invalid_file_upload'], 400);
                    }
                    $path = storage_path() . '/img/servico/' . $servico->id . '/';
                    $file_name = 'perfil.png';
                    $file->move($path, $file_name);
                    $servico->imagem = 'storage/img/servico/' . $servico->id . '/' . $file_name;
                }
                $servico->save();
                if ($request->ativo == false) {
                    $this->destroy($id);
                }
                return response()->json(['Ok'], 200);
            }
        }
        return response()->json(["erro"], 500);
    }


    public
    function destroy($id) {
        $user = Auth::user();

        $servico = Servico::findOrFail($id);
        if ($this->permite_alterar_servico($user, $servico))
            if ($servico->delete()) {
                return response()->json(['Ok'], 200);
            }
        return response()->json(['Erro'], 400);
    }

    public
    function restore($id) {
        $user = Auth::user();

        $servico = Servico::onlyTrashed()->findOrFail($id);
        if ($this->permite_alterar_servico($user, $servico))
            if ($servico->restore()) {
                return response()->json(['Ok'], 200);
            }
        return response()->json(['Erro'], 400);
    }

    private
    function permite_alterar_servico($user, Servico $servico) {
        return $user->salao_id == $servico->salao_id
            && ($user->is_dono_salao || $servico->users->pluck('id')->contains($user->id));
    }
}
