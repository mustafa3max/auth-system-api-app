<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as RulesPassword;

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
            'password' => ['required', 'max:12', RulesPassword::min(4)->letters()->numbers()->mixedCase()->symbols()],
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
            event(new Registered($user));


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

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink($validator->validate());

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Email verification link has been sent.',
                    'data' => null,
                ]
            );
        }
        return response()->json(
            [
                'status' => false,
                'message' => 'There is an error. The email verification link was not sent.',
                'data' => null,
            ]
        );
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => ['required', 'max:12', RulesPassword::min(4)->letters()->numbers()->mixedCase()->symbols()],
            'token' => 'required',
        ]);

        $status = Password::reset(
            $validator->validate(),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'New password has been set successfully.',
                    'data' => null,
                ]
            );
        } else {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'There is an error, a new password has not been set.',
                    'data' => null,
                ]
            );
        }
    }
}
