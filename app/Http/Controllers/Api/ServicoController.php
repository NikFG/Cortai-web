<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServicoController extends Controller {
    private $base_storage = 'images/servico/';

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
        if (empty($servicos)) {
            return response()->json([], 204);
        }
        foreach ($servicos as $s) {
            if ($s->imagem != null)
                try {
                    $s->imagem = base64_encode(Storage::cloud()->get($s->imagem));
                } catch (FileNotFoundException $e) {
                    return response()->json(['Arquivo não encontrado'], 500);
                }
        }

        return response()->json($servicos);
    }

    public function servicoSalao($idSalao) {
        $user = Auth::user();
        $servicos = Servico::where('salao_id', $idSalao)
            ->has('cabeleireiros')
            ->with('cabeleireiros')
            ->orderBy('nome')
            ->get();
        foreach ($servicos as $s) {
            if ($s->imagem != null)
                try {
                    $s->imagem = base64_encode(Storage::cloud()->get($s->imagem));
                } catch (FileNotFoundException $e) {
                    return response()->json(['Arquivo não encontrado'], 500);
                }
        }

        return response()->json($servicos);
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
        foreach ($servicos as $s) {
            if ($s->imagem != null)
                try {
                    $s->imagem = base64_encode(Storage::cloud()->get($s->imagem));
                } catch (FileNotFoundException $e) {
                    return response()->json(['Arquivo não encontrado'], 500);
                }
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
        foreach ($servicos as $s) {
            if ($s->imagem != null)
                try {
                    $s->imagem = base64_encode(Storage::cloud()->get($s->imagem));
                } catch (FileNotFoundException $e) {
                    return response()->json(['Arquivo não encontrado'], 500);
                }
        }
        return response()->json($servicos, 200);
    }

    public function store(Request $request) {
        $user = Auth::user();
        $cab = [];
        if ($user->is_dono_salao || $user->is_cabeleireiro) {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:75',
                'valor' => 'required|numeric',
                'observacao' => 'nullable|string',
                'cabeleireiros' => 'required|array',
                'cabeleireiros.*.id' => 'exists:users,id',
                'imagem' => 'file|nullable',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            $servico = new Servico();
            $servico->nome = $request->nome;
            $servico->valor = $request->valor;
            $servico->observacao = $request->observacao;
            $servico->salao()->associate($user->salao_id);

            if ($servico->save()) {
                foreach ($request->cabeleireiros as $c) {
                    $cab = Arr::prepend($cab, (int)$c['id']);
                }

                $servico->cabeleireiros()->sync($cab);
                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    if (!$file->isValid()) {
                        return response()->json(['invalid_file_upload'], 400);
                    }
                    $file_name = $this->base_storage . $servico->id . '/' . 'perfil.' . $file->getClientOriginalExtension();
                    Storage::cloud()->put($file_name, file_get_contents($file));
                    $servico->imagem = $file_name;
                    $servico->save();
                }
            }

            return response()->json(['Ok'], 200);
        }
        return response()->json(["erro"], 500);

    }


    public function show(int $id) {

        $servico = Servico::where('id', $id)->with('users')->get();
        if ($servico->imagem != null)
            try {
                $servico->imagem = base64_encode(Storage::cloud()->get($servico->imagem));
            } catch (FileNotFoundException $e) {
                return response()->json(['Arquivo não encontrado'], 500);
            }
        return response()->json($servico, 200);
    }


    public function update(Request $request, $id) {
        $user = Auth::user();
        $cab = [];
        if ($user->is_dono_salao || $user->is_cabeleireiro) {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:75',
                'valor' => 'required|numeric',
                'observacao' => 'nullable|string',
                'cabeleireiros' => 'required|array',
                'cabeleireiros.*.id' => 'exists:users,id',
                'imagem' => 'file|nullable',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $servico = Servico::findOrFail($id);
            if ($this->permite_alterar_servico($user, $servico)) {
                $servico->nome = $request->nome;
                $servico->valor = $request->valor;
                $servico->observacao = $request->observacao;
                $servico->salao_id = $user->salao_id;

                foreach ($request->cabeleireiros as $c) {
                    $cab = Arr::prepend($cab, (int)$c['id']);
                }
                $servico->cabeleireiros()->sync($cab);
                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    if (!$file->isValid()) {
                        return response()->json(['imagem' => 'invalid_file_upload'], 422);
                    }
                    $file_name = $this->base_storage . $servico->id . '/' . 'perfil.' . $file->getClientOriginalExtension();
                    Storage::cloud()->put($file_name, file_get_contents($file));

                    $servico->imagem = $file_name;
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


    public function destroy($id) {
        $user = Auth::user();

        $servico = Servico::findOrFail($id);
        if ($this->permite_alterar_servico($user, $servico))
            if ($servico->delete()) {
                return response()->json(['Ok'], 200);
            }
        return response()->json(['Erro'], 400);
    }

    public function restore($id) {
        $user = Auth::user();

        $servico = Servico::onlyTrashed()->findOrFail($id);
        if ($this->permite_alterar_servico($user, $servico))
            if ($servico->restore()) {
                return response()->json(['Ok'], 200);
            }
        return response()->json(['Erro'], 400);
    }

    private function permite_alterar_servico($user, Servico $servico) {
        return $user->salao_id == $servico->salao_id
            && ($user->is_dono_salao || $servico->users->pluck('id')->contains($user->id));
    }
}
