<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewMemberNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Broadcast notifikasi member baru ke semua admin
        $this->broadcastNewMember($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Broadcast notifikasi member baru ke semua admin (kecuali yang baru daftar).
     */
    private function broadcastNewMember(User $user): void
    {
        $adminUsers = User::whereNotNull('role')
            ->where('id', '!=', $user->id)
            ->get();

        foreach ($adminUsers as $adminUser) {
            $adminUser->notify(new NewMemberNotification([
                'name'        => $user->name,
                'email'       => $user->email,
                'created_at'  => now()->format('d/m/Y H:i'),
            ]));
        }
    }
}
