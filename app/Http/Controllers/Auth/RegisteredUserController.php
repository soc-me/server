<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:15', 'min:5', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        //set user bio
        $user->bio = 'Hello, I am new here!';
        $user->is_private = False;
        $user->isAdmin = False;
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }

    /**
     * Handle an incoming delete request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destroy(Request $request): Response
    {
        // Checks whether its the GUEST account
        if (Auth::user()->name == 'GUEST') {
            return response(['message' => 'Cannot delete the GUEST account'], 403);
        }
        $currUser = Auth::user();
        $currUser->delete();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->noContent();
    }
}
