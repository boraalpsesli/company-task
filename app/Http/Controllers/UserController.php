<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Jobs\DownloadUsers;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

    public function download()
    {
        $user = Auth::user();
        
        // Only users with view users permission can download users
        if (!$user->can('view users')) {
            return response()->json([
                'message' => 'Unauthorized to download users'
            ], 403);
        }

        // Generate a unique filename
        $filename = 'users_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = ['ID', 'Name', 'Email', 'Created At'];
        foreach (range('A', 'D') as $index =>   $column) {
            $sheet->setCellValue($column . '1', $headers[$index]);
        }
        
        // Style the header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        
        // Get all users
        $users = \App\Models\User::all();
        
        // Add data
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->id);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValue('D' . $row, $user->created_at->format('Y-m-d H:i:s'));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create writer and save file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'users_');
        $writer->save($tempFile);
        
        // Save to storage
        Storage::disk('public')->put('exports/' . $filename, file_get_contents($tempFile));
        
        // Clean up temp file
        unlink($tempFile);
        
        return response()->json([
            'message' => 'Users downloaded successfully',
            'file' => asset('storage/exports/' . $filename)
        ]);
    }

    public function listExports()
    {
        $user = Auth::user();
        
        // Only users with view users permission can list exports
        if (!$user->can('view users')) {
            return response()->json([
                'message' => 'Unauthorized to view exports'
            ], 403);
        }

        $files = Storage::disk('public')->files('exports');
        $exports = collect($files)->map(function ($file) {
            return [
                'name' => basename($file),
                'url' => asset('storage/' . $file),
                'created_at' => Storage::disk('public')->lastModified($file)
            ];
        });

        return response()->json([
            'exports' => $exports
        ]);
    }
}
