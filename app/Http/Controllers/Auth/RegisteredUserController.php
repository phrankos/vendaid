<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // $record = Record::create([
        //     'student_number' => 0,
        //     'type_id' => 1,
        //     'last_name' => '',
        //     'first_name' => '',
        //     'middle_name' => '',
        //     'maiden_name' => '',
        //     'email' => '',
        //     'alt_email' => '',
        //     'phone_number' => '',
        //     'address' => '',
        //     'sex_id' => 1,
        //     'suffix_id' => 1,
        //     'latin_honors_id' => 1,
        //     'batch' => 0,
        // ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3,
        ]);
        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
