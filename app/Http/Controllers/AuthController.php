<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('Token Name')->accessToken;
                return redirect()->route('dashboard');
            } else {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function showLoginForm()
    {
        return view('login.login');
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout(); // Logout pengguna
            $request->session()->invalidate();
            $request->session()->regenerateToken();
    
            return redirect('/login')->with('success', 'Successfully logged out');
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
