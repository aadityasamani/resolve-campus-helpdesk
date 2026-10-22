<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller {
    public function loginForm() { return view('auth',['register'=>false]); }
    public function registerForm() { return view('auth',['register'=>true]); }
    public function login(Request $r) {
        $data=$r->validate(['email'=>'required|email','password'=>'required|string']);
        if (!Auth::attempt($data)) return back()->withErrors(['email'=>'The email or password is incorrect.'])->onlyInput('email');
        $r->session()->regenerate(); return redirect()->intended(route('dashboard'))->with('success','Welcome back. Let’s make campus better.');
    }
    public function register(Request $r) {
        $data=$r->validate(['name'=>'required|string|max:80','email'=>'required|email|max:254|unique:users','password'=>['required','confirmed',Password::min(8)]]);
        $user=User::create($data); Auth::login($user); $r->session()->regenerate();
        return redirect()->route('dashboard')->with('success','Your account is ready.');
    }
    public function logout(Request $r) {
        Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken();
        return redirect()->route('login')->with('success','You have signed out.');
    }
}
