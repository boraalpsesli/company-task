<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TeamService;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('manage teams')) {
            return response()->json([
                'message' => 'Unauthorized to create teams'
            ], 403);
        }

        $result = $this->teamService->create($request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'team' => $result['team']
        ], 201);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('view teams')) {
            return response()->json([
                'message' => 'Unauthorized to view teams'
            ], 403);
        }

        $perPage = $request->query('per_page', 10);
        $result = $this->teamService->getAll($perPage);

        return response()->json([
            'teams' => $result['teams']
        ], 200);
    }

    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->can('view teams')) {
            return response()->json([
                'message' => 'Unauthorized to view teams'
            ], 403);
        }

        $result = $this->teamService->getById($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'team' => $result['team']
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->can('edit teams')) {
            return response()->json([
                'message' => 'Unauthorized to update teams'
            ], 403);
        }

        $result = $this->teamService->update($id, $request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], $result['errors'] ? 422 : 404);
        }

        return response()->json([
            'message' => $result['message'],
            'team' => $result['team']
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->can('delete teams')) {
            return response()->json([
                'message' => 'Unauthorized to delete teams'
            ], 403);
        }

        $result = $this->teamService->delete($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'message' => $result['message']
        ], 200);
    }
} 