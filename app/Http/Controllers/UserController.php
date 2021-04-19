<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller {
    private $base_storage = 'images/user/';

    public function index() {
        $user = User::all();

        return response()->json($user);
    }


    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'nome' => 'required|string',
            'password' => 'required|string',
            'telefone' => 'required',
            'imagem' => 'file|nullable',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user != null) {
            return response()->json([], 409);
        }
        $user = new User();
        $user->nome = $request->nome;
        $user->password = bcrypt($request->password);
        $user->email = $request->email;
        $user->telefone = $request->telefone;
        if ($request->hasFile('imagem')) {
            $file = $request->file('imagem');
            if (!$file->isValid()) {
                return response()->json(['invalid_file_upload'], 400);
            }
            $file_name = $this->base_storage . $user->id . '/' . 'perfil.' . $file->getClientOriginalExtension();
            Storage::cloud()->put($file_name, file_get_contents($file));
            $user->imagem = $file_name;
        }
        $user->save();
        $user->sendEmailVerificationNotification();
        return response()->json('Login criado com sucesso! Verifique seu email', 201);

    }


    public function show($id) {

    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|max:75',
            'telefone' => 'required|celular_com_ddd',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::findOrFail($id);
        $user->nome = $request->nome;
        $user->telefone = $request->telefone;
        if ($user->save()) {
            return response()->json(['Ok'], 200);
        }
        return response()->json('Erro', 403);
    }

    public function updateImage(Request $request, $id) {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'imagem' => 'file|required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if ($request->hasFile('imagem')) {
            $file = $request->file('imagem');
            if (!$file->isValid()) {
                return response()->json(['invalid_file_upload'], 400);
            }
            $file_name = $this->base_storage . $user->id . '/' . 'perfil.' . $file->getClientOriginalExtension();
            Storage::cloud()->put($file_name, file_get_contents($file));
            $user->imagem = $file_name;
            $user->save();
            return response()->json('Imagem enviada corretamente', 201);
        }
        return response()->json('Imagem não enviada corretamente', 500);
    }

    public function destroy($id) {

    }
}
