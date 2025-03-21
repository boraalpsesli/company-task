<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;

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
            'token' => $result['token']
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
        $perPage = $request->query('per_page', 10);
        $result = $this->userService->getAllUsers($perPage);

        return response()->json([
            'users' => $result['users']
        ], 200);
    }
}
