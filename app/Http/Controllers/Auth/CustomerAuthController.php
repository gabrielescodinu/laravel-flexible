<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Access\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.customer-login');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectAfterAuth($request);
    }

    public function showRegister(): View
    {
        return view('auth.customer-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => trim((string) $validated['name']),
            'email' => trim((string) $validated['email']),
            'password' => (string) $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('account.orders.index'))
            ->with('success', 'Account creato con successo.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }

    private function redirectAfterAuth(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('customer.login');
        }

        if ($this->isAdminUser($user)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('account.orders.index'));
    }

    private function isAdminUser(User $user): bool
    {
        if ((int) $user->id === 1) {
            return true;
        }

        if ($user->roles()->exists() || $user->permissions()->exists()) {
            return $user->hasAnyPermission(Permissions::allNames());
        }

        return false;
    }
}
