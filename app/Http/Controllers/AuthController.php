<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(
                [
                    'status' => true,
                    'message' =>  null,
                    'data' => [
                        'token' => $request->user()->createToken($request->user()->id)->plainTextToken,
                        'verified' => $request->user()->email_verified_at != null,
                    ],
                ]
            );
        }
        return response()->json(
            [
                'status' => false,
                'message' => 'There is an error. You are not logged in',
                'data' => null,
            ]
        );
    }

    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|regex:/^[\pL\s]+$/u',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'max:12', Password::min(4)->letters()->numbers()->mixedCase()->symbols()],
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
                'data' => null,
            ]);
        }

        $user = User::create([
            'name' => $validator->validate()['name'],
            'email' => $validator->validate()['email'],
            'password' => Hash::make($validator->validate()['password']),
        ]);

        if (boolval($user)) {
            return response()->json(
                [
                    'status' => true,
                    'message' =>  null,
                    'data' => [
                        'token' => $user->createToken($user->id)->plainTextToken,
                        'verified' => $user->email_verified_at != null,
                    ],
                ]
            );
        }
        return response()->json(
            [
                'status' => false,
                'message' => 'There is an error. The account was not created',
                'data' => null,
            ]
        );
    }
}
