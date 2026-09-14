<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::all(),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    /**
     * DR-012 (handler half, CQ-003): only an admin session may create a
     * user account.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $this->validated($request, requirePassword: true);

        User::create($validated);

        return redirect()->route('users.index');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user]);
    }

    /**
     * DR-012 (handler half, CQ-003): only an admin session may update a
     * user account. DR-013/CQ-006: a blank submitted password leaves the
     * stored hash unchanged - it is never written as an empty value.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $this->validated($request, requirePassword: false, ignoreUserId: $user->id);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index');
    }

    /**
     * DR-012 (handler half, CQ-003): only an admin session may delete a
     * user account. CQ-021: this must actually delete, not silently no-op.
     * User carries no relationship edge to any other entity (domain-model.md),
     * so no referential-integrity check applies here (unlike Room/Customer).
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin($request);

        $user->delete();

        return redirect()->route('users.index');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()->type === User::TYPE_ADMIN, 403);
    }

    private function validated(Request $request, bool $requirePassword, ?int $ignoreUserId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($ignoreUserId),
            ],
            'password' => [$requirePassword ? 'required' : 'nullable', 'min:8'],
            'type' => ['required', Rule::in([User::TYPE_ADMIN, User::TYPE_STAFF])],
        ]);
    }
}
