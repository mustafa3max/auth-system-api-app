<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
                'data' => null,
            ]);
        }
        switch ($validator->validate()['type']) {
            case 'image':
                $validator = Validator::make($request->all(), [
                    'bytes' => 'required|string',
                    'extension' => 'required|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => $validator->errors(),
                        'data' => null,
                    ]);
                }
                $update = $this->updateAvatar($request->user(), $validator->validate()['bytes'], $validator->validate()['extension']);

                return response()->json([
                    'status' => $update,
                    'message' => $update ? 'Your profile picture has been updated successfully' : 'There is an error. The profile picture has not been updated',
                    'data' => null,
                ]);
            case 'name':
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => $validator->errors(),
                        'data' => null,
                    ]);
                }

                $update = $request->user()->update([
                    'name' => $validator->validate()['name']
                ]);

                $update = boolval($update);

                return response()->json([
                    'status' => $update,
                    'message' => $update ? 'The name has been updated successfully' : 'There is an error. The name was not updated',
                    'data' => null,
                ]);
        }
    }

    public function delete(Request $request)
    {
        if (Auth::check()) {
            if (boolval($request->user()->delete())) {
                return response()->json(
                    [
                        'status' => true,
                        'message' =>  'Your account has been successfully deleted',
                        'data' => null,
                    ]
                );
            }
            return response()->json(
                [
                    'status' => false,
                    'message' =>  'There is an error. Your account has not been deleted',
                    'data' => null,
                ]
            );
        }
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

    private function updateAvatar(User $user, $bytes,  $extension)
    {
        $path = $user->id . '/avatars/avatar.' . $extension;

        Storage::disk('assets-users')->put($path, base64_decode($bytes));

        $update = User::where('id', Auth::id())->update([
            'avatar' => $path,
        ]);

        return boolval($update);
    }
}
