<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
    // 📋 Get All Users
    public function index()
    {
        try {
            $users = User::with('roles')->latest()->paginate(15);
            return ApiResponse::success($users, 'User list fetched');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    // ➕ Create User
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'nullable|string|unique:users,phone',
                'address' => 'nullable|string',
                'password' => 'required|string|min:8',
                'role' => 'nullable|string|exists:roles,name'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('role')) {
                $user->assignRole($request->role);
            }

            $user->load('roles');

            return ApiResponse::success($user, 'User created successfully');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    // 🔍 Show Single User
    public function show(User $user)
    {
        try {
            $user->load('roles');
            return ApiResponse::success($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    // ✏️ Update User
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|unique:users,phone,' . $user->id,
                'address' => 'nullable|string',
                'password' => 'nullable|string|min:8',
                'role' => 'nullable|string|exists:roles,name'
            ]);

            $user->update([
                'name' => $request->name ?? $user->name,
                'email' => $request->email ?? $user->email,
                'phone' => $request->phone ?? $user->phone,
                'address' => $request->address ?? $user->address,
                'password' => $request->filled('password') 
                    ? Hash::make($request->password) 
                    : $user->password,
            ]);

            if ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }

            $user->load('roles');

            return ApiResponse::success($user, 'User updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    // ❌ Delete User
    public function destroy(User $user)
    {
        try {
            // prevent deleting self (optional safety)
            if (auth()->id() === $user->id) {
                return ApiResponse::error('You cannot delete yourself');
            }

            $user->tokens()->delete();
            $user->delete();

            return ApiResponse::success(null, 'User deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}