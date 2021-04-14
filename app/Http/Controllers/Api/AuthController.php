<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller {
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
//                $user->imagem = base64_encode(Storage::cloud()->get($user->imagem));
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

    public function loginGoogle2(Request $request) {
        $user = Socialite::driver('google')->userFromToken($request->password);
        return response()->json(["u" => $user, "request" => $request->all()]);
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
            $user->imagem = $google_user->avatar;
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
        if ($user != null) {
            Password::sendResetLink($credentials);
            return response()->json(["msg" => 'Reset password link sent on your email id.']);
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
            return response()->json(["msg" => "Invalid token provided"], 403);
        }

        return response()->json(["msg" => "Password has been successfully changed"]);
    }
}
