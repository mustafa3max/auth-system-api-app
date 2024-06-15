<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientControll extends Controller
{
    public function index(Request $request)
    {
        return Client::where('info', 'LIKE', "%{$request->search}%")
            ->simplePaginate(10);
    }

    public function show(Request $request)
    {
        return Client::find($request->client_id);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'location' => 'required|string',
            'section' => 'required|string',
            'specialization' => 'required|string',
            'specialization_type' => 'required|string',
            'governorate' => 'required|string',
            'info' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
                'data' => null,
            ]);
        }

        $create =  Client::create([
            'user_id' => Auth::id(),
            'phone' => $validator->validate()['phone'],
            'location' => $validator->validate()['location'],
            'section' => $validator->validate()['section'],
            'specialization' => $validator->validate()['specialization'],
            'specialization_type' => $validator->validate()['specialization_type'],
            'governorate' => $validator->validate()['governorate'],
            'info' => $validator->validate()['info'],
        ]);

        if (boolval($create)) {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'The client was created successfully.',
                    'data' => null,
                ]
            );
        }

        return response()->json(
            [
                'status' => false,
                'message' => 'There is an error that the client was not created.',
                'data' => null,
            ]
        );
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'key' => 'required|string',
            'value' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
                'data' => null,
            ]);
        }
        $isUpdate = true;
        if ($validator->validate()['key'] != 'phone') {
            if ($validator->validate()['key'] != 'location') {
                if ($validator->validate()['key'] != 'section') {
                    if ($validator->validate()['key'] != 'specialization') {
                        if ($validator->validate()['key'] != 'specialization_type') {
                            if ($validator->validate()['key'] != 'governorate') {
                                if ($validator->validate()['key'] != 'info') {
                                    $isUpdate = false;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($isUpdate) {
            $update =  Client::where('user_id', Auth::id())->where('id', $request->client_id)->update(
                [$validator->validate()['key'] => $validator->validate()['value']]
            );

            if (boolval($update)) {
                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Data updated successfully.',
                        'data' => null,
                    ]
                );
            }
        }

        return response()->json(
            [
                'status' => false,
                'message' => 'There is an error. The data was not updated.',
                'data' => null,
            ]
        );
    }

    public function delete(Request $request)
    {
        $delete =  Client::where('user_id', Auth::id())->where('id', $request->client_id)->delete();

        if (boolval($delete)) {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'The client was created successfully.',
                    'data' => null,
                ]
            );
        }

        return response()->json(
            [
                'status' => false,
                'message' => 'The client has been successfully deleted.',
                'data' => null,
            ]
        );
    }
}
