<?php

namespace App\Http\Controllers\api;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $createTokenSeed = 'myTestOAuth';

    public function login(Request $request)
    {
        if (Auth::attempt(
            [
                'email' => $request->email,
                'password' => $request->password,
            ]
        )) {
            $user = Auth::user();

            $token = $user->createToken($this->createTokenSeed);

            return response()->json(
                [
                    'success' => true,
                    'access_token' => $token->accessToken,
                    'token_type' => 'Bearer',
                    'expires_at' => Carbon::parse(
                        $token->token->expires_at
                    )->toDateTimeString()
                ],
                200
            );
        }

        return response()->json(
            [
                'error' => 'Unauthorised'
            ], 401
        );
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                're_password' => 'required|same:password',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                ['error' => $validator->errors()],
                401
            );
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken($this->createTokenSeed)->accessToken;
        $success['name'] = $user->name;

        return response()->json(
            ['success' => $success],
            200
        );
    }

    public function details()
    {
        $user = Auth::user();

        return response()->json(
            ['success' => $user],
            200
        );
    }
}
