<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller {

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
            'imagem' => '',
        ]);
        if ($validator->fails()) {
            return response()->json('Erro', 406);
        }

        $user = new User();
        $user->nome = $request->nome;
        $user->password = bcrypt($request->password);
        $user->email = $request->email;
        $user->telefone = $request->telefone;
        $user->imagem = $request->imagem;
        $user->save();
        $user->sendEmailVerificationNotification();
        return response()->json('Login criado com sucesso! Verifique seu email', 200);

    }


    public function show($id) {

    }

    public function update(Request $request, $id) {

        $validator = Validator::make($request->all(), [
            'nome' => 'required',
            'telefone' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json('Erro', 406);
        }
        $user = User::findOrFail($id);
        $user->nome = $request->nome;
        $user->telefone = $request->telefone;
        if ($user->save()) {
            return response()->json(['Ok'], 200);
        }
        return response()->json('Erro', 406);
    }


    public function destroy($id) {

    }
}
