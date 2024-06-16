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
                    'message' => $update ? __('done.update_avatar') : __('error.update_avatar'),
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
                    'message' => $update ? __('done.update_name') : __('error.update_name'),
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
                        'message' =>  __('done.delete_account'),
                        'data' => null,
                    ]
                );
            }
            return response()->json(
                [
                    'status' => false,
                    'message' =>  __('error.delete_account'),
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
                    'message' =>  __('done.sign_out'),
                    'data' => null,
                ]
            );
        }
        return response()->json(
            [
                'status' => false,
                'message' => __('error.sign_out'),
                'data' => null,
            ]
        );
    }

    public function verifyEmail(Request $request)
    {
        if (!$request->user()->hasVerifiedEmail()) {
            try {
                $request->user()->sendEmailVerificationNotification();
            } catch (\Throwable $th) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => __('error.verify_email_sent'),
                        'data' => null,
                    ]
                );
            }
        }

        return response()->json(
            [
                'status' => false,
                'message' => __('error.verify_email_already'),
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
