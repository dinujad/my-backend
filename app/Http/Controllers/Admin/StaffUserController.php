<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffUserController extends Controller
{
    public function index(): View
    {
        $staff = User::query()
            ->where('role', '!=', 'customer')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'roles' => User::assignableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys(User::assignableRoles()))],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created.');
    }

    public function edit(User $user): View
    {
        $this->guardTarget($user);

        return view('admin.staff.edit', [
            'staffUser' => $user,
            'roles' => $this->rolesFor($user),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardTarget($user);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys($this->rolesFor($user)))],
        ]);

        if ($user->is($request->user()) && $data['role'] !== $user->role) {
            return back()->with('error', 'You cannot change your own role.')->withInput();
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return redirect()->route('admin.staff.index')->with('success', 'Staff account updated.');
    }

    private function guardTarget(User $user): void
    {
        if ($user->role === 'customer') {
            abort(404);
        }

        if ($user->role === 'super_admin' && auth()->user()?->role !== 'super_admin') {
            abort(403);
        }
    }

    /** @return array<string, string> */
    private function rolesFor(User $user): array
    {
        $roles = User::assignableRoles();
        if ($user->role === 'super_admin') {
            $roles = ['super_admin' => 'Super Admin'] + $roles;
        }

        return $roles;
    }
}
