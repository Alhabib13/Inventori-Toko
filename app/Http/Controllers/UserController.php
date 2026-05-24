<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $owner = $request->user();
        $users = $this->managedUsersQuery($owner)->orderBy('role')->orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
            'isSimpleMode' => $owner?->mode_app === 'sederhana',
            'ownerCount' => $users->where('role', 'owner')->count(),
            'cashierCount' => $users->where('role', 'kasir')->count(),
            'warehouseCount' => $users->where('role', 'gudang')->count(),
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $user = $this->managedUser($request->user(), $user);

        return view('users.show', compact('user'));
    }

    public function edit(Request $request, User $user): View
    {
        $owner = $request->user();
        $user = $this->editableUser($owner, $user);

        return view('users.edit', [
            'user' => $user,
            'allowedRoles' => $this->allowedUserRolesForMode($owner?->mode_app),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $owner = $request->user();
        $user = $this->editableUser($owner, $user);
        $allowedRoles = $this->allowedUserRolesForMode($owner?->mode_app);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash:ascii', Rule::unique('users', 'username')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys($allowedRoles))],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->forceFill($data)->save();

        return redirect()
            ->route('users.show', $user)
            ->with('status', 'Data user berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $owner = $request->user();
        $user = $this->editableUser($owner, $user);

        $user->forceFill([
            'is_active' => ! $user->is_active,
        ])->save();

        return redirect()
            ->route('users.index')
            ->with('status', $user->is_active ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.');
    }

    private function managedUsersQuery(?User $owner)
    {
        $allowedRoles = array_keys($this->allowedUserRolesForMode($owner?->mode_app));

        return User::query()
            ->where('store_name', $owner?->store_name)
            ->where(function ($query) use ($owner, $allowedRoles): void {
                $query->where('id', $owner?->id)
                    ->orWhereIn('role', $allowedRoles);
            });
    }

    private function managedUser(?User $owner, User $user): User
    {
        return $this->managedUsersQuery($owner)
            ->whereKey($user->id)
            ->firstOrFail();
    }

    private function editableUser(?User $owner, User $user): User
    {
        $user = $this->managedUser($owner, $user);

        if ($user->role === 'owner') {
            abort(403, 'Owner tidak dapat diubah melalui modul ini.');
        }

        return $user;
    }

    /**
     * @return array<string, string>
     */
    private function allowedUserRolesForMode(?string $modeApp): array
    {
        return match ($modeApp) {
            'sederhana' => ['kasir' => 'Kasir'],
            'lengkap' => ['kasir' => 'Kasir', 'gudang' => 'Gudang'],
            default => [],
        };
    }
}
