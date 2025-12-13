<?php
// app/Services/Admin/UserService.php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function getFilteredUsers(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        $query->latest();

        return $query->paginate($perPage);
    }

    public function createUser(array $data, User $admin): User
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'user',
                'email_verified_at' => now(),
            ]);

            Log::info('Utilisateur créé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'created_by' => $admin->id
            ]);

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création utilisateur', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateUser(User $user, array $data, User $admin): bool
    {
        DB::beginTransaction();

        try {
            $user->name = $data['name'];
            $user->email = $data['email'];
            
            if (!empty($data['role'])) {
                $user->role = $data['role'];
            }
            
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            
            $user->save();

            Log::info('Utilisateur mis à jour', [
                'user_id' => $user->id,
                'updated_by' => $admin->id
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour utilisateur', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function deleteUser(User $user, User $admin): bool
    {
        try {
            Log::warning('Suppression utilisateur', [
                'user_id' => $user->id,
                'deleted_by' => $admin->id
            ]);

            $user->delete();
            return true;

        } catch (\Exception $e) {
            Log::error('Erreur suppression utilisateur', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'users' => User::where('role', 'user')->count(),
            'this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }
}