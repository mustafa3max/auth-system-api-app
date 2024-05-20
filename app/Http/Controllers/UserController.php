<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function profile()
    {
        return response()->json(
            [
                'status' => true,
                'message' =>  null,
                'data' => Auth::user(),
            ]
        );
    }

    public function update(Request $request)
    {
    }

    public function delete(Request $request)
    {
    }

    public function signOut()
    {
        if (Auth::check()) {
            session()->flush();

            auth('web')->logout();

            return response()->json(
                [
                    'status' => true,
                    'message' =>  'You are logged out',
                    'data' => null,
                ]
            );
        }
        return response()->json(
            [
                'status' => false,
                'message' => 'There is an error. You are not logged out',
                'data' => null,
            ]
        );
    }
}
