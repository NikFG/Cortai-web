<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Galeria;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GaleriaController extends Controller {

    private $base_storage = 'images/galeria/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($salao_id) {
        $galeria = Galeria::with('salao')
            ->with('cabeleireiro')
            ->with('servico')
            ->with('cliente')
            ->where('salao_id', $salao_id)
            ->get();
        foreach ($galeria as $g) {
            if ($g->imagem != null)
                try {
                    $g->imagem = base64_encode(Storage::cloud()->get($g->imagem));
                } catch (FileNotFoundException $e) {
                    return response()->json(['Arquivo não encontrado'], 500);
                }
        }
        return response()->json($galeria);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string|max:500',
            'imagem' => 'required|file',
            'salao' => 'required',
            'servico' => 'required',
            'cabeleireiro' => 'required',
            'cliente' => 'nullable',
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $galeria = new Galeria();
        $galeria->salao()->associate($request->salao['id']);
        $galeria->servico()->associate($request->servico['id']);
        $galeria->cabeleireiro()->associate($request->cabeleireiro['id']);
        if ($request->cliente != null)
            $galeria->cliente()->associate($request->cliente['id']);
        $galeria->descricao = $request->descricao;
        if ($request->hasFile('imagem')) {
            $file = $request->file('imagem');
            if (!$file->isValid()) {
                return response()->json(['invalid_file_upload'], 400);
            }
            $file_name = $this->base_storage . $galeria->salao_id . '/' . date('mdYHis') . uniqid() . '.png';
            Storage::cloud()->put($file_name, file_get_contents($file));
            $galeria->imagem = $file_name;
            if ($galeria->save())
                return response()->json('Ok', 201);

        }
        return response()->json('Erro', 500);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id) {
        $galeria = Galeria::findOrFail($id);
        try {
            $galeria->imagem = base64_encode(Storage::cloud()->get($galeria->imagem));
        } catch (FileNotFoundException $e) {
            return response()->json(['Arquivo não encontrado' => $e], 500);
        }
        return response()->json($galeria);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id) {
        $galeria = Galeria::findOrFail($id);
        Storage::cloud()->delete($galeria->imagem);
        $galeria->delete();
        return response()->json('Deletado', 200);
    }
}
