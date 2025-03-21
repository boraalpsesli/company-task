<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService
{
    public function login(array $credentials)
    {
        $validator = Validator::make($credentials, [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        $user = Auth::user();
        $token = $user->createToken(
            name: 'personal-token',
            expiresAt: now()->addDay(),
            abilities: ['*']
        )->plainTextToken;

        return [
            'success' => true,
            'token' => $token
        ];
    }

    public function register(array $data)
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
            'name' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string', 'max:255'],
            'team_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'name' => $data['name'],
            'national_id' => $data['national_id'],
            'company_id' => $data['company_id'],
            'team_id' => $data['team_id'],
        ]);

        return [
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user
        ];
    }
} 