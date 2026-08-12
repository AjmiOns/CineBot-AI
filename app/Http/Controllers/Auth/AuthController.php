<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\MovieInteraction;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // GET /register — Affiche le formulaire d'inscription
    // ─────────────────────────────────────────────────────────────────────
    public function showRegister(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /register — Traite l'inscription
    // ─────────────────────────────────────────────────────────────────────
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'email.unique'          => 'An account already exists with this email address.',
            'password.confirmed'    => 'Password confirmation does not match.',
            'password.min'          => 'Password must be at least 8 characters.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('chatbot'))
            ->with('success', 'Welcome to CineBot AI, ' . $user->name . ' 🎬');
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /login — Affiche le formulaire de connexion
    // ─────────────────────────────────────────────────────────────────────
    public function showLogin(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /login — Traite la connexion
    // ─────────────────────────────────────────────────────────────────────
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match any account.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('chatbot'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /logout — Déconnexion
    // ─────────────────────────────────────────────────────────────────────
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /forgot-password — Formulaire de demande de réinitialisation
    // ─────────────────────────────────────────────────────────────────────
    public function showForgotPassword(): \Illuminate\View\View
    {
        return view('auth.forgot-password');
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /forgot-password — Envoie le lien de réinitialisation par email
    // ─────────────────────────────────────────────────────────────────────
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // On renvoie toujours le même message, que l'email existe ou non,
        // pour ne pas révéler quelles adresses sont enregistrées (énumération).
        Password::sendResetLink($request->only('email'));

        return back()->with('success',
            "If an account exists with this address, a password reset email has just been sent."
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /reset-password/{token} — Formulaire de nouveau mot de passe
    // ─────────────────────────────────────────────────────────────────────
    public function showResetPassword(Request $request, string $token): \Illuminate\View\View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /reset-password — Applique le nouveau mot de passe
    // ─────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min'       => 'Password must be at least 8 characters.',
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'This password reset link is invalid or has expired.',
            ]);
        }

        return redirect()->route('login')
            ->with('success', 'Your password has been reset. You can now log in.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE /profile — Suppression définitive du compte et de ses données
    // (droit à l'oubli). Confirmation par mot de passe obligatoire.
    // ─────────────────────────────────────────────────────────────────────
    public function destroyAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Incorrect password.',
            ]);
        }

        $userId = $user->id;

        DB::transaction(function () use ($userId) {
            ChatHistory::where('user_id', $userId)->delete();
            UserPreference::where('user_id', $userId)->delete();
            MovieInteraction::where('user_id', $userId)->delete();
            User::where('id', $userId)->delete();
        });

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Your account and all your data have been permanently deleted.');
    }
}
