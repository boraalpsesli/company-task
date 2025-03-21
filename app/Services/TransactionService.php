<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Team;
use Illuminate\Support\Facades\Validator;

class TransactionService
{
    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'team_id' => 'required|exists:teams,id',
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'category' => 'required|string',
            'reference_number' => 'nullable|string|unique:transactions,reference_number'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $team = Team::findOrFail($data['team_id']);
        
        $transaction = Transaction::create([
            'amount' => $data['amount'],
            'description' => $data['description'],
            'team_id' => $data['team_id'],
            'type' => $data['type'],
            'date' => $data['date'],
            'category' => $data['category'],
            'reference_number' => $data['reference_number'] ?? null
        ]);

        return [
            'success' => true,
            'message' => 'Transaction created successfully',
            'transaction' => $transaction->load('team')
        ];
    }

    public function getAll($perPage = 10)
    {
        $transactions = Transaction::with('team')
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        return [
            'success' => true,
            'transactions' => $transactions
        ];
    }

    public function getById($id)
    {
        $transaction = Transaction::with('team')->find($id);

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }

        return [
            'success' => true,
            'transaction' => $transaction
        ];
    }

    public function update($id, array $data)
    {
        $validator = Validator::make($data, [
            'amount' => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|required|string',
            'team_id' => 'sometimes|required|exists:teams,id',
            'type' => 'sometimes|required|in:income,expense',
            'date' => 'sometimes|required|date',
            'category' => 'sometimes|required|string',
            'reference_number' => 'nullable|string|unique:transactions,reference_number,' . $id
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $transaction = Transaction::find($id);

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }

        if (isset($data['team_id'])) {
            $team = Team::findOrFail($data['team_id']);
        }

        $transaction->update($data);

        return [
            'success' => true,
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction->load('team')
        ];
    }

    public function delete($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }

        $transaction->delete();

        return [
            'success' => true,
            'message' => 'Transaction deleted successfully'
        ];
    }

    public function getTeamTransactions($teamId, $perPage = 10)
    {
        $transactions = Transaction::where('team_id', $teamId)
            ->with('team')
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        return [
            'success' => true,
            'transactions' => $transactions
        ];
    }
} 