<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login(Request $request)
    {
        $result = $this->userService->login($request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], 401);
        }

        return response()->json([
            'message' => $result['message'],
            'email' => $result['email']
        ], 200);
    }

    public function register(Request $request)
    {
        $result = $this->userService->register($request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'user' => $result['user']
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        
        // Check if user is viewing their own profile or has view users permission
        if ($user->id != $id && !$user->can('view users')) {
            return response()->json([
                'message' => 'Unauthorized to view this user'
            ], 403);
        }

        $result = $this->userService->getUser($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'user' => $result['user']
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if user is updating their own profile or has edit users permission
        if ($user->id != $id && !$user->can('edit users')) {
            return response()->json([
                'message' => 'Unauthorized to update this user'
            ], 403);
        }

        $result = $this->userService->updateUser($id, $request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], $result['errors'] ? 422 : 404);
        }

        return response()->json([
            'message' => $result['message'],
            'user' => $result['user']
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        // Only users with delete users permission can delete users
        if (!$user->can('delete users')) {
            return response()->json([
                'message' => 'Unauthorized to delete users'
            ], 403);
        }

        $result = $this->userService->deleteUser($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'message' => $result['message']
        ], 200);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only users with view users permission can view all users
        if (!$user->can('view users')) {
            return response()->json([
                'message' => 'Unauthorized to view all users'
            ], 403);
        }

        $perPage = $request->query('per_page', 10);
        $result = $this->userService->getAllUsers($perPage);

        return response()->json([
            'users' => $result['users']
        ], 200);
    }

    public function assignPermissions(Request $request, $id)
    {
        $user = Auth::user();
        
        // Only users with manage users permission can assign permissions
        if (!$user->can('manage users')) {
            return response()->json([
                'message' => 'Unauthorized to assign permissions'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->userService->assignPermissions($id, $request->permissions);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'message' => $result['message'],
            'user' => $result['user']
        ], 200);
    }

    public function checkPermissions()
    {
        $user = Auth::user();
        
        return response()->json([
            'user' => $user->name,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'can_view_users' => $user->can('view users'),
            'can_manage_users' => $user->can('manage users')
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $result = $this->userService->verifyOtp($request->all());
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], 401);
        }

        return response()->json([
            'message' => $result['message'],
            'token' => $result['token'],
            'user' => $result['user']
        ], 200);
    }
}
