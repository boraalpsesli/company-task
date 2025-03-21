<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Company;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;

class TeamService
{
    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id'
        ]);
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $company = Company::findOrFail($data['company_id']);
        
        $team = Team::create([
            'name' => $data['name'],
            'company_id' => $data['company_id']
        ]);

        return [
            'success' => true,
            'message' => 'Team created successfully',
            'team' => $team->load('company')
        ];
    }

    public function getAll($perPage = 10)
    {
        $teams = Team::with('company')
            ->paginate($perPage);

        return [
            'success' => true,
            'teams' => $teams
        ];
    }

    public function getById($id)
    {
        $team = Team::with('company')->find($id);

        if (!$team) {
            return [
                'success' => false,
                'message' => 'Team not found'
            ];
        }

        return [
            'success' => true,
            'team' => $team
        ];
    }

    public function update($id, array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'company_id' => 'sometimes|required|exists:companies,id'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $team = Team::find($id);

        if (!$team) {
            return [
                'success' => false,
                'message' => 'Team not found'
            ];
        }

        if (isset($data['company_id'])) {
            $company = Company::findOrFail($data['company_id']);
        }

        $team->update($data);

        return [
            'success' => true,
            'message' => 'Team updated successfully',
            'team' => $team->load('company')
        ];
    }

    public function delete($id)
    {
        $team = Team::find($id);

        if (!$team) {
            return [
                'success' => false,
                'message' => 'Team not found'
            ];
        }

        $team->delete();

        return [
            'success' => true,
            'message' => 'Team deleted successfully'
        ];
    }

    public function getStatistics($id, $companyId = null)
    {
        $team = Team::find($id);

        if (!$team) {
            return [
                'success' => false,
                'message' => 'Team not found'
            ];
        }

        // Verify team belongs to the correct company
        if ($companyId && $team->company_id !== $companyId) {
            return [
                'success' => false,
                'message' => 'Unauthorized to access this team'
            ];
        }

        // Base query for transactions
        $baseQuery = Transaction::where('team_id', $team->id);
        
        // Income transactions
        $incomeQuery = clone $baseQuery;
        $incomeTransactions = $incomeQuery->where('type', 'income');
        
        // Expense transactions
        $expenseQuery = clone $baseQuery;
        $expenseTransactions = $expenseQuery->where('type', 'expense');
        
        $stats = [
            'total_users' => $team->users()->count(),
            'total_transactions' => $baseQuery->count(),
            'income_transactions' => $incomeTransactions->count(),
            'expense_transactions' => $expenseTransactions->count(),
            'total_income' => $incomeTransactions->sum('amount') ?? 0,
            'total_expenses' => $expenseTransactions->sum('amount') ?? 0,
        ];

        return [
            'success' => true,
            'statistics' => $stats
        ];
    }

    public function getAllStatistics($companyId = null)
    {
        $query = Team::query();
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Get all teams with their user counts
        $teams = $query->withCount(['users', 'transactions'])->get();
        
        $stats = [
            'total_teams' => $teams->count(),
            'total_users' => $teams->sum('users_count'),
            'teams_with_users' => $teams->where('users_count', '>', 0)->count(),
            'teams_without_users' => $teams->where('users_count', 0)->count(),
            'teams_with_transactions' => $teams->where('transactions_count', '>', 0)->count(),
            'teams_without_transactions' => $teams->where('transactions_count', 0)->count(),
        ];

        return [
            'success' => true,
            'statistics' => $stats
        ];
    }
} 