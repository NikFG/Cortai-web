<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
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


    protected function respondWithToken($token) {
        $user = JWTAuth::setToken($token)->toUser();

        if ($user->email_verified_at != null || $user->is_google) {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user,
            ], 200);
        }
        JWTAuth::setToken($token)->invalidate();
        $user->sendEmailVerificationNotification();
        return response()->json(['Email não verificado, olhe sua caixa de entrada ou spam'], 403);
    }

    /* criar login google */
    public function loginGoogle(Request $request) {
        $credentials = $request->only(['email', 'password']);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'nome' => 'string',
            'password' => 'required|string',
            'telefone' => '',
            'imagem' => '',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            $user = new User();
            $user->nome = $request->nome;
            $user->password = bcrypt($request->password);
            $user->email = $request->email;
            $user->telefone = $request->telefone;
            $user->imagem = $request->imagem;
            $user->is_google = true;
            $user->save();
        }
        $token = auth('api')->login($user);


        return $this->respondWithToken($token);
    }

    public function logout() {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function resetPassword(Request $request) {

        //TODO tratar se email existe
        $credentials = $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->only('email'))->firstOrFail();
        Password::sendResetLink($credentials);
        return response()->json(["msg" => 'Reset password link sent on your email id.']);
    }

    public function reset() {
        $credentials = request()->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json(["msg" => "Invalid token provided"], 403);
        }

        return response()->json(["msg" => "Password has been successfully changed"]);
    }
}
