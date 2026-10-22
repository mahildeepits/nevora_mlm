<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TreePlacementService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected TreePlacementService $treePlacementService;
    protected OtpService $otpService;

    public function __construct(TreePlacementService $treePlacementService, OtpService $otpService)
    {
        $this->treePlacementService = $treePlacementService;
        $this->otpService = $otpService;
    }

    public function loginForm()
    {
        if (Auth::guard('web')->check() || Auth::guard('member')->check()) {
            return redirect()->route('member.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required', // can be member_id or email
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $loginInput = $request->input('login');
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'member_id';

        $user = User::where($field, $loginInput)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Invalid credentials provided.'])->withInput();
        }

        // Check if admin login attempted on member portal
        if ($user->role_id === 1) {
            Auth::guard('admin')->login($user);
            return redirect()->route('admin.dashboard');
        }

        Auth::guard('web')->login($user);
        return redirect()->route('member.dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
    }

    public function registerForm(Request $request)
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('member.invite')->with('info', 'You are logged in. You can register and invite downline members here.');
        }

        $sponsorId = $request->get('sponsor');
        $sponsorName = null;

        if ($sponsorId) {
            $sponsor = User::where('member_id', $sponsorId)->first();
            if ($sponsor && $sponsor->is_paid) {
                $sponsorName = $sponsor->name;
            } else {
                $sponsorId = null;
            }
        }

        return view('auth.register', compact('sponsorId', 'sponsorName'));
    }

    public function sendRegisterOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $res = $this->otpService->generateAndSend($request->email, 'registration');
        return response()->json($res);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sponsor_id' => 'required|exists:users,member_id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'password' => 'required|min:6|confirmed',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Rule: Only active (is_paid = 1) users can sponsor!
        $sponsor = User::where('member_id', $request->sponsor_id)->first();
        if (!$sponsor || !$sponsor->is_paid) {
            return back()->withErrors(['sponsor_id' => 'Invalid sponsor. Only active/paid members can refer new members.'])->withInput();
        }

        // Verify Email OTP
        if (!$this->otpService->verify($request->email, $request->otp, 'registration')) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code.'])->withInput();
        }

        // Create new user (unpaid status by default)
        $memberId = generateMemberId();

        $user = User::create([
            'member_id' => $memberId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'sponsor_id' => $sponsor->member_id,
            'role_id' => 2,
            'is_paid' => false, // placed immediately in tree, activates at will!
            'current_slot_id' => 1,
        ]);

        // Place immediately in 3-Matrix working tree under sponsor
        $this->treePlacementService->placeInWorkingTree($user, $sponsor);

        Auth::guard('web')->login($user);

        return redirect()->route('member.dashboard')->with('success', 'Registration successful! Your Member ID is ' . $memberId . '. Please bind your BEP-20 wallet and activate Slot 1.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login.form')->with('success', 'You have been logged out.');
    }
}
