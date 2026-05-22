<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return Auth::user()->isLecturer()
            ? redirect()->intended(route('lecturer.dashboard'))
            : redirect()->intended(route('dashboard'));
    })->name('login.store');

    Route::post('register', function (Request $request) {
        $request->merge([
            'email' => strtolower((string) $request->input('email')),
        ]);

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'nim_nip' => ['nullable', 'string', 'max:255', 'unique:users,nim_nip'],
            'role' => ['required', 'in:student,lecturer'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create($attributes);

        Auth::login($user);

        $request->session()->regenerate();

        return $user->isLecturer()
            ? redirect()->route('lecturer.dashboard')
            : redirect()->route('dashboard');
    })->name('register.store');
});

Route::post('logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');
