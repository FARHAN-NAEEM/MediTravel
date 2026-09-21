<?php

namespace App\Http\Controllers;

use App\Models\ReferralAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AgentAuthController extends Controller
{
    public function login()
    {
        return view('referrals.auth', ['mode' => 'login']);
    }

    public function forgot()
    {
        return view('referrals.auth', ['mode' => 'forgot']);
    }

    public function reset(Request $request, string $token)
    {
        return view('referrals.auth', ['mode' => 'reset', 'token' => $token, 'email' => $request->query('email')]);
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:190', 'password' => 'required|string|max:4096']);
        $data['email'] = strtolower(trim($data['email']));
        $key = 'agent-login:'.hash('sha256', $data['email'].'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'কিছুক্ষণ পরে আবার চেষ্টা করুন।']);
        }
        if (! Auth::guard('agent')->attempt($data + ['is_active' => true])) {
            RateLimiter::hit($key, 300);

            return back()->withErrors(['email' => 'ইমেইল বা পাসওয়ার্ড সঠিক নয় অথবা অ্যাকাউন্ট নিষ্ক্রিয়।'])->onlyInput('email');
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('agent_session_version', Auth::guard('agent')->user()->session_version);

        return redirect()->route('agent.dashboard');
    }

    public function email(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:190']);
        try {
            Password::broker('agents')->sendResetLink(['email' => strtolower(trim($data['email'])), 'is_active' => true]);
        } catch (\Throwable $e) {
            // Never expose SMTP responses, addresses or reset tokens to the browser/log.
            logger()->warning('Agent password email failed.', ['exception_type' => $e::class]);
        }

        return back()->with('success', 'সক্রিয় অ্যাকাউন্ট থাকলে পাসওয়ার্ডের লিংক পাঠানো হবে। ইনবক্স ও স্প্যাম দেখুন।');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:190', 'token' => 'required|string|max:255', 'password' => ['required', 'string', 'max:4096', 'confirmed', PasswordRule::defaults()]]);
        $data['email'] = strtolower(trim($data['email']));
        $status = Password::broker('agents')->reset($data + ['is_active' => true], function (ReferralAgent $agent, string $password) {
            $agent->forceFill(['password' => $password, 'remember_token' => Str::random(60), 'session_version' => $agent->session_version + 1])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('agent.login')->with('success', 'পাসওয়ার্ড সেট হয়েছে। নতুন পাসওয়ার্ড দিয়ে লগইন করুন।')
            : back()->withErrors(['email' => 'লিংকটি মেয়াদোত্তীর্ণ বা অকার্যকর। নতুন লিংক অনুরোধ করুন।']);
    }

    public function logout(Request $request)
    {
        Auth::guard('agent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('agent.login');
    }
}
