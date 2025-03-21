<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('manage transactions')) {
            return response()->json([
                'message' => 'Unauthorized to create transactions'
            ], 403);
        }

        $result = $this->transactionService->create($request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'transaction' => $result['transaction']
        ], 201);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('view transactions')) {
            return response()->json([
                'message' => 'Unauthorized to view transactions'
            ], 403);
        }

        $perPage = $request->query('per_page', 10);
        $result = $this->transactionService->getAll($perPage);

        return response()->json([
            'transactions' => $result['transactions']
        ], 200);
    }

    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->can('view transactions')) {
            return response()->json([
                'message' => 'Unauthorized to view transactions'
            ], 403);
        }

        $result = $this->transactionService->getById($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'transaction' => $result['transaction']
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->can('edit transactions')) {
            return response()->json([
                'message' => 'Unauthorized to update transactions'
            ], 403);
        }

        $result = $this->transactionService->update($id, $request->all());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null
            ], $result['errors'] ? 422 : 404);
        }

        return response()->json([
            'message' => $result['message'],
            'transaction' => $result['transaction']
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->can('delete transactions')) {
            return response()->json([
                'message' => 'Unauthorized to delete transactions'
            ], 403);
        }

        $result = $this->transactionService->delete($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'message' => $result['message']
        ], 200);
    }

    public function teamTransactions(Request $request, $teamId)
    {
        $user = Auth::user();
        
        if (!$user->can('view transactions')) {
            return response()->json([
                'message' => 'Unauthorized to view transactions'
            ], 403);
        }

        $perPage = $request->query('per_page', 10);
        $result = $this->transactionService->getTeamTransactions($teamId, $perPage);

        return response()->json([
            'transactions' => $result['transactions']
        ], 200);
    }
} 