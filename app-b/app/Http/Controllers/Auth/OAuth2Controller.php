<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OAuth2Controller extends Controller
{
    public function loginOrRegisterUseSilvia(array $request)
    {
        $this->guard()->logout();
        $user = User::where('email', $request['email'])->first();
        if (!$user) {
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'role' => $request['role'],
                'password' => $request['password'],
            ]);
        }
        $this->guard()->login($user);
        return redirect()->route('home');
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
