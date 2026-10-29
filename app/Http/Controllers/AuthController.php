<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TreePlacementService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        // Support member_id directly (with fallback to login field for backward compatibility)
        if (!$request->has('member_id') && $request->has('login')) {
            $request->merge(['member_id' => $request->input('login')]);
        }

        $validator = Validator::make($request->all(), [
            'member_id' => 'required',
            'password' => 'required',
        ], [
            'member_id.required' => 'Please enter your Member ID.',
            'password.required' => 'Please enter your password.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $memberId = trim($request->input('member_id'));

        $user = User::where('member_id', $memberId)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['member_id' => 'Invalid Member ID or password provided.'])->withInput();
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
        $loggedInUser = Auth::guard('web')->user() ?? Auth::guard('member')->user();

        $sponsorId = $request->get('sponsor') ?: $request->get('ref');
        $sponsorName = null;

        // If sponsorId is not specified in URL, but a user is logged in, default sponsorId to the logged in user
        if (!$sponsorId && $loggedInUser && $loggedInUser->is_paid) {
            $sponsorId = $loggedInUser->member_id;
            $sponsorName = $loggedInUser->name;
        } elseif ($sponsorId) {
            $sponsor = User::where('member_id', $sponsorId)->first();
            if ($sponsor && $sponsor->is_paid) {
                $sponsorName = $sponsor->name;
            } else {
                $sponsorId = null;
            }
        }

        return view('auth.register', compact('sponsorId', 'sponsorName', 'loggedInUser'));
    }

    /**
     * Check Sponsor validity via AJAX (for dynamic form verification)
     */
    public function checkSponsor(Request $request)
    {
        $sponsorId = trim($request->get('sponsor_id', ''));

        if (!$sponsorId) {
            return response()->json(['status' => false, 'message' => 'Please enter a Sponsor Member ID.']);
        }

        $sponsor = User::where('member_id', $sponsorId)->first();

        if (!$sponsor) {
            return response()->json(['status' => false, 'message' => 'Sponsor Member ID does not exist in NEVORA.']);
        }

        if (!$sponsor->is_paid) {
            return response()->json(['status' => false, 'message' => 'This sponsor account is inactive. Only active members can refer.']);
        }

        return response()->json([
            'status' => true,
            'sponsor_name' => $sponsor->name,
            'sponsor_id' => $sponsor->member_id,
        ]);
    }

    /**
     * 1-Click Web3 Login (SafePal / Trust Wallet / MetaMask / etc.)
     */
    public function loginWeb3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_address' => [
                'required',
                'regex:/^0x[a-fA-F0-9]{40}$/',
            ],
        ], [
            'wallet_address.required' => 'Please connect a valid Web3 wallet.',
            'wallet_address.regex' => 'Invalid BEP-20 wallet address format.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $address = strtolower(trim($request->wallet_address));
        $user = User::where('wallet_address', $address)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No NEVORA account is registered with this wallet address. Please register first.'
            ], 404);
        }

        Auth::guard('web')->login($user);

        return response()->json([
            'status' => true,
            'message' => 'Welcome back, ' . $user->name . '!',
            'redirect' => route('member.dashboard')
        ]);
    }

    public function register(Request $request)
    {
        $alreadyLoggedIn = Auth::guard('web')->user() ?? Auth::guard('member')->user();

        $validator = Validator::make($request->all(), [
            'sponsor_id' => 'required|exists:users,member_id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'mobile' => 'required|string|max:20|unique:users,mobile',
            'password' => 'required|min:6|confirmed',
            'wallet_address' => [
                'required',
                'regex:/^0x[a-fA-F0-9]{40}$/',
            ],
        ], [
            'mobile.required' => 'Mobile number is required.',
            'mobile.unique' => 'This mobile number is already registered to another account. Please use a unique phone number.',
            'wallet_address.required' => 'Please connect your SafePal or Web3 wallet to complete registration.',
            'wallet_address.regex' => 'Please connect a valid BSC (BEP-20) wallet address starting with 0x (42 characters).',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Prevent registration for admin accounts
        if (User::where('email', $request->email)->where('role_id', 1)->exists()) {
            $msg = 'This email address is reserved and cannot be used.';
            return ($request->expectsJson() || $request->ajax())
                ? response()->json(['status' => false, 'message' => $msg], 422)
                : back()->withErrors(['email' => $msg])->withInput();
        }

        // Check if wallet address is already linked to another account
        $walletAddress = strtolower(trim($request->wallet_address));
        $existingWallet = User::where('wallet_address', $walletAddress)->first();
        if ($existingWallet) {
            if (!$alreadyLoggedIn) {
                // If not logged in, log into existing user
                Auth::guard('web')->login($existingWallet);
                $msg = 'This wallet is already registered. Logging you in...';
                return ($request->expectsJson() || $request->ajax())
                    ? response()->json(['status' => true, 'is_existing' => true, 'message' => $msg, 'redirect' => route('member.dashboard')])
                    : redirect()->route('member.dashboard')->with('info', $msg);
            } else {
                // If already logged in, notify that wallet belongs to another account
                $msg = 'This wallet address is already linked to another member (' . $existingWallet->member_id . '). Please connect a unique wallet.';
                return ($request->expectsJson() || $request->ajax())
                    ? response()->json(['status' => false, 'message' => $msg], 422)
                    : back()->withErrors(['wallet_address' => $msg])->withInput();
            }
        }

        // Rule: Only active (is_paid = 1) users can sponsor!
        $sponsor = User::where('member_id', $request->sponsor_id)->first();
        if (!$sponsor || !$sponsor->is_paid) {
            $msg = 'Invalid sponsor. Only active/paid members can refer new members.';
            return ($request->expectsJson() || $request->ajax())
                ? response()->json(['status' => false, 'message' => $msg], 422)
                : back()->withErrors(['sponsor_id' => $msg])->withInput();
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
            'wallet_address' => $walletAddress,
        ]);

        // Place immediately in 3-Matrix working tree under sponsor
        $this->treePlacementService->placeInWorkingTree($user, $sponsor);

        // Send Welcome Email
        try {
            Mail::send('emails.welcome', ['user' => $user, 'sponsor' => $sponsor], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Welcome to NEVORA - Account Created');
            });
        } catch (\Throwable $th) {
            Log::warning("Welcome email sending skipped/failed for {$user->email}: " . $th->getMessage());
        }

        // Condition 1: If a user was ALREADY logged in on this device (e.g. sponsor), keep them logged in!
        if ($alreadyLoggedIn) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'is_already_logged_in' => true,
                    'current_user_name' => $alreadyLoggedIn->name,
                    'current_user_id' => $alreadyLoggedIn->member_id,
                    'member_id' => $memberId,
                    'name' => $user->name,
                    'sponsor_id' => $sponsor->member_id,
                    'wallet_address' => $walletAddress,
                    'message' => "Member {$user->name} (ID: {$memberId}) has been successfully registered under Sponsor {$sponsor->member_id}!",
                    'redirect' => route('member.network.directs')
                ]);
            }

            return redirect()->route('member.network.directs')->with('success', "Member {$user->name} (ID: {$memberId}) successfully registered under {$sponsor->member_id}!");
        }

        // Condition 2: No user was logged in (brand new visitor), log in directly!
        Auth::guard('web')->login($user);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'is_already_logged_in' => false,
                'is_existing' => false,
                'message' => 'Registration successful! Your Member ID is ' . $memberId . '.',
                'member_id' => $memberId,
                'redirect' => route('member.dashboard')
            ]);
        }

        return redirect()->route('member.dashboard')->with('success', 'Registration successful! Your Member ID is ' . $memberId . '. Please activate Slot 1 ($15 USDT) to start receiving payments.');
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
