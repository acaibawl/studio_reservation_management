<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerAuth\LoginPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerAuthController extends Controller
{
    public function login(LoginPost $request): JsonResponse
    {
        $credentials = $request->validated();
        // email・password（自動でハッシュする）で検索をかけて、一致するownerがいればtokenを設定。なければfalseが入る
        if (! $token = auth()->guard('api_owner')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json($user);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'owner_access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl'),
        ]);
    }
}
