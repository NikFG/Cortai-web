<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller {
    private string $base_storage = 'images/users/';

    public function login(Request $request) {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    private function respondWithToken($token): JsonResponse {
        $user = JWTAuth::setToken($token)->toUser();
        if ($user->email_verified_at != null || $user->is_google) {
            if ($user->imagem != null)
                $user->imagem = base64_encode(Storage::cloud()->get($user->imagem));
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user,
            ]);
        }
        JWTAuth::setToken($token)->invalidate();
        $user->sendEmailVerificationNotification();
        return response()->json('Email não verificado, olhe sua caixa de entrada ou spam', 403);
    }


    /* criar login google */
    public function loginGoogle(Request $request): JsonResponse {
        $credentials = $request->only(['email', 'token']);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $google_user = Socialite::driver('google')->userFromToken($request->token);
        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            $user = new User();
            $user->nome = $google_user->name;
            $user->password = bcrypt($request->token);
            $user->email = $google_user->email;
            $user->is_google = true;
            $user->save();
            $this->googleAvatar($google_user->getAvatar(), $user);
        }

        $token = auth('api')->login($user);


        return $this->respondWithToken($token);
    }

    private function googleAvatar($file, $user) {
        $file_name = $this->base_storage . $user->id . '/' . 'perfil.jpg';
        Storage::cloud()->put($file_name, file_get_contents($file));
        $user->imagem = $file_name;
        $user->save();
    }

    public function logout() {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function resetPassword(Request $request) {

        $credentials = $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->only('email'))->first();
        if ($user != null) {
            $status = Password::sendResetLink(
                $request->only('email')
            );
            return response()->json(["status" => $status, "msg" => 'Reset password link sent on your email id.']);
        } else {
            return response()->json(["msg" => "Usuário não encontrado"], 401);
        }
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
            return response()->json(["msg" => "Token inválido"], 403);
        }

        return response()->json(["msg" => "Senha alterada com sucesso"]);
    }
}
