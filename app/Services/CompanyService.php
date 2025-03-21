<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Validator;

class CompanyService
{
    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $company = Company::create($data);

        return [
            'success' => true,
            'message' => 'Company created successfully',
            'company' => $company
        ];
    }

    public function getAll($perPage = 10)
    {
        $companies = Company::with('teams')->paginate($perPage);

        return [
            'success' => true,
            'companies' => $companies
        ];
    }

    public function getById($id)
    {
        $company = Company::with('teams')->find($id);

        if (!$company) {
            return [
                'success' => false,
                'message' => 'Company not found'
            ];
        }

        return [
            'success' => true,
            'company' => $company
        ];
    }

    public function update($id, array $data)
    {
        $company = Company::find($id);

        if (!$company) {
            return [
                'success' => false,
                'message' => 'Company not found'
            ];
        }

        $validator = Validator::make($data, [
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $company->update($data);

        return [
            'success' => true,
            'message' => 'Company updated successfully',
            'company' => $company
        ];
    }

    public function delete($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return [
                'success' => false,
                'message' => 'Company not found'
            ];
        }

        $company->delete();

        return [
            'success' => true,
            'message' => 'Company deleted successfully'
        ];
    }
} 