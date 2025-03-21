<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use App\Notifications\LoginOppNotification;
use Illuminate\Support\Facades\Notification;


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

        // Instead of generating token here, send OTP
        $user = Auth::user();
        return $this->sendOtp($user);
    }

    private function sendOtp($user)
    {
        $otp = rand(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        $user->notify(new LoginOppNotification($otp));
        return [
            'success' => true,
            'message' => 'OTP sent successfully. Please verify to complete login.',
            'email' => $user->email
        ];
    }

    public function register(array $data)
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
            'name' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'exists:companies,id'],
            'team_id' => ['required', 'exists:teams,id'],
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

        // Assign default permissions
        $user->givePermissionTo([
            'view own profile',
            'edit own profile'
        ]);

        return [
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user->load('permissions')
        ];
    }

    public function getUser($id)
    {
        $user = User::with('permissions')->find($id);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        return [
            'success' => true,
            'user' => $user
        ];
    }

    public function verifyOtp(array $data)
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email'],
            'otp' => ['required', 'numeric', 'digits:6'],
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        if ($user->otp !== $data['otp']) {
            return [
                'success' => false,
                'message' => 'Invalid OTP'
            ];
        }

        if ($user->otp_expires_at < now()) {
            return [
                'success' => false,
                'message' => 'OTP has expired'
            ];
        }

        // Clear OTP after successful verification
        $user->update([
            'otp' => null,
            'otp_expires_at' => null
        ]);

        // Generate token only after OTP verification
        $token = $user->createToken(
            name: 'personal-token',
            expiresAt: now()->addDay(),
            abilities: $user->getAllPermissions()->pluck('name')->toArray()
        )->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user->load('permissions')
        ];
    }

    public function updateUser($id, array $data)
    {
        $user = User::find($id);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $validator = Validator::make($data, [
            'email' => ['email', 'unique:users,email,' . $id],
            'password' => ['min:8'],
            'name' => ['string', 'max:255'],
            'national_id' => ['string', 'max:255'],
            'company_id' => ['string', 'max:255'],
            'team_id' => ['string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $updateData = array_filter($data, function($value) {
            return !is_null($value);
        });

        if (isset($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        $user->update($updateData);

        return [
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user->load('permissions')
        ];
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $user->delete();

        return [
            'success' => true,
            'message' => 'User deleted successfully'
        ];
    }

    public function getAllUsers($perPage = 10)
    {
        $users = User::with('permissions')->paginate($perPage);

        return [
            'success' => true,
            'users' => $users
        ];
    }

    public function assignPermissions($userId, array $permissions)
    {
        $user = User::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Validate that all permissions exist
        $existingPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        if (count($existingPermissions) !== count($permissions)) {
            return [
                'success' => false,
                'message' => 'One or more permissions do not exist'
            ];
        }

        $user->syncPermissions($permissions);

        return [
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'user' => $user->load('permissions')
        ];
    }
} 