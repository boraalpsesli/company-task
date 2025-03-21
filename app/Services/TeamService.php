<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Company;
use Illuminate\Support\Facades\Validator;

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
} 