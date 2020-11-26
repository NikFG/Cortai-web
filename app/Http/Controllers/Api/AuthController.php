<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller {
    public function login(Request $request) {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function loginCriado($credentials) {

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token) {
        $user = JWTAuth::setToken($token)->toUser();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user,
        ], 200);
    }

    /* criar login google */
    public function loginGoogle(Request $request) {
        $credentials = $request->only(['email', 'nome', 'password']);
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'nome' => 'required|string',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $u = User::where('email', $request->email)->first();
        if ($u == null)
            $u = new User();
        $u->nome = $request->nome;
        $u->email = $request->email;
        $u->google_token = $request->password;
        $u->save();
        if (!$token = auth('api')->login($u)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    public function logout() {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function criaConta(Request $request) {

    }
}
