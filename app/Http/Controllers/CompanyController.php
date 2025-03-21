<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('manage companies')) {
            return response()->json([
                'message' => 'Unauthorized to create companies'
            ], 403);
        }

        $result = $this->companyService->create($request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'company' => $result['company']
        ], 201);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('view companies')) {
            return response()->json([
                'message' => 'Unauthorized to view companies'
            ], 403);
        }

        $perPage = $request->query('per_page', 10);
        $result = $this->companyService->getAll($perPage);

        return response()->json([
            'companies' => $result['companies']
        ], 200);
    }

    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->can('view companies')) {
            return response()->json([
                'message' => 'Unauthorized to view companies'
            ], 403);
        }

        $result = $this->companyService->getById($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'company' => $result['company']
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->can('edit companies')) {
            return response()->json([
                'message' => 'Unauthorized to update companies'
            ], 403);
        }

        $result = $this->companyService->update($id, $request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], $result['errors'] ? 422 : 404);
        }

        return response()->json([
            'message' => $result['message'],
            'company' => $result['company']
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->can('delete companies')) {
            return response()->json([
                'message' => 'Unauthorized to delete companies'
            ], 403);
        }

        $result = $this->companyService->delete($id);

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