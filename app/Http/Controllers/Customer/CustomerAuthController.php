<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        return view('customer.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));
        $key = 'customer-otp:' . sha1($email . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'email' => 'Too many OTP requests. Please try again later.',
            ]);
        }

        RateLimiter::hit($key, 600);

        // Customer OTP login must never be used to authenticate
        // administrator or vendor accounts.
        $user = User::where('email', $email)->first();

        if ($user && $user->role !== 'customer') {
            return back()
                ->with('status', 'If this email is eligible, an OTP will be sent.')
                ->withInput(['email' => $email]);
        }

        if ($user && ! $user->is_active) {
            return back()
                ->with('status', 'If this email is eligible, an OTP will be sent.')
                ->withInput(['email' => $email]);
        }

        $existingOtp = DB::table('customer_login_otps')
            ->where('email', $email)
            ->first();

        if (
            $existingOtp &&
            now()->diffInSeconds($existingOtp->last_sent_at) < 60
        ) {
            throw ValidationException::withMessages([
                'email' => 'Please wait before requesting another OTP.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('customer_login_otps')->updateOrInsert(
            ['email' => $email],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'last_sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        try {
            Mail::raw(
                "Your B2B customer login OTP is: {$code}\n\n"
                . "This code expires in 10 minutes. "
                . "Do not share it with anyone.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Your customer login verification code');
                }
            );
        } catch (\Throwable $exception) {
            report($exception);

            DB::table('customer_login_otps')
                ->where('email', $email)
                ->delete();

            throw ValidationException::withMessages([
                'email' => 'We could not send the verification email. Please try again later.',
            ]);
        }

        return back()
            ->with('status', 'If this email is eligible, a verification code has been sent.')
            ->with('otp_sent', true)
            ->with('otp_email', $email);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = Str::lower(trim($validated['email']));

        $user = DB::transaction(function () use ($email, $validated) {
            $otp = DB::table('customer_login_otps')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (! $otp) {
                throw ValidationException::withMessages([
                    'code' => 'The verification code is invalid or expired.',
                ]);
            }

            if (now()->greaterThan($otp->expires_at)) {
                DB::table('customer_login_otps')
                    ->where('email', $email)
                    ->delete();

                throw ValidationException::withMessages([
                    'code' => 'The verification code has expired. Request a new one.',
                ]);
            }

            if ($otp->attempts >= 5) {
                DB::table('customer_login_otps')
                    ->where('email', $email)
                    ->delete();

                throw ValidationException::withMessages([
                    'code' => 'Too many incorrect attempts. Request a new code.',
                ]);
            }

            if (! Hash::check($validated['code'], $otp->code_hash)) {
                DB::table('customer_login_otps')
                    ->where('email', $email)
                    ->increment('attempts');

                throw ValidationException::withMessages([
                    'code' => 'The verification code is incorrect.',
                ]);
            }

            $user = User::where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($user && ($user->role !== 'customer' || ! $user->is_active)) {
                throw ValidationException::withMessages([
                    'email' => 'This account cannot sign in as a customer.',
                ]);
            }

            if (! $user) {
                $user = User::create([
                    'name' => Str::before($email, '@'),
                    'email' => $email,
                    'password' => Str::random(64),
                    'role' => 'customer',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            } elseif (! $user->email_verified_at) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            DB::table('customer_login_otps')
                ->where('email', $email)
                ->delete();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        RateLimiter::clear('customer-otp:' . sha1($email . '|' . $request->ip()));

        return redirect()->route('customer.account')
            ->with('status', 'You are now signed in.');
    }

    public function account()
    {
        abort_unless(auth()->user()?->role === 'customer', 403);

        return view('customer.account');
    }

    public function logout(Request $request)
    {
        abort_unless(auth()->user()?->role === 'customer', 403);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.index')
            ->with('status', 'You have been signed out.');
    }
}
