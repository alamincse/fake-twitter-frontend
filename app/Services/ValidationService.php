<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Participant;

class ValidationService
{
    public function validateLogin(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
            ],
            'password' => 'required',
        ]);
    }

    public function validateRegister(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                'unique:participants',
            ],
            'password' => 'required|string',
        ]);
    }

    public function validateProfile(Request $request, $participantId): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                'unique:participants,email,'.$participantId,
            ],
            'password' => 'required|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            'timeline_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);
    }

    public function validateTweet(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'tweet' => 'required|string',
        ]);
    }

    public function doRegister(Request $request)
    {
        $data = [
            'name' => data_get($request, 'name'),
            'email' => data_get($request, 'email'),
            'password' => Hash::make(data_get($request, 'password')),
        ];

        return Participant::create($data);
    }
}
