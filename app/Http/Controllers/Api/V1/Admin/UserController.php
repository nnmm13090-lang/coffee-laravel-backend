<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** GET /admin/users?role=staff&search=dara */
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->role, fn ($q, $r) => $q->role($r))
            ->when($request->search, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
            ))
            ->latest('id')
            ->paginate(20);

        return UserResource::collection($users);
    }

    /** GET /admin/roles - roles with their permissions */
    public function roles(): JsonResponse
    {
        $roles = Role::with('permissions')->get()->map(fn ($role) => [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->values(),
        ]);

        return response()->json(['data' => $roles]);
    }

    /** PUT /admin/users/{user}/role  { "role": "staff" } */
    public function updateRole(Request $request, User $user): UserResource
    {
        $data = $request->validate([
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages(['role' => ['You cannot change your own role.']]);
        }

        $user->syncRoles($data['role']);

        return new UserResource($user->load('roles'));
    }
}
