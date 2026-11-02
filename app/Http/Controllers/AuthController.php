<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Services\TreePlacementService;
use App\Services\OtpService;
use App\Services\PaymentRouteService;
use App\Services\P2PBlockchainService;
use App\Services\MatrixIncomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected TreePlacementService $treePlacementService;
    protected OtpService $otpService;
    protected PaymentRouteService $paymentRouteService;
    protected P2PBlockchainService $blockchainService;
    protected MatrixIncomeService $matrixIncomeService;

    public function __construct(
        TreePlacementService $treePlacementService,
        OtpService $otpService,
        PaymentRouteService $paymentRouteService,
        P2PBlockchainService $blockchainService,
        MatrixIncomeService $matrixIncomeService
    ) {
        $this->treePlacementService = $treePlacementService;
        $this->otpService = $otpService;
        $this->paymentRouteService = $paymentRouteService;
        $this->blockchainService = $blockchainService;
        $this->matrixIncomeService = $matrixIncomeService;
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

        $stashed = null;
        $token = $request->get('token');
        if ($token) {
            try {
                $stashed = Cache::store('file')->get('reg_stash_' . $token);
            } catch (\Throwable $e) {
                $stashed = Cache::get('reg_stash_' . $token);
            }
        }

        $sponsorId = $request->get('sponsor') ?: ($request->get('ref') ?: ($stashed['sponsor_id'] ?? null));
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

        $autoConnect = $request->get('auto_connect');

        return view('auth.register', compact('sponsorId', 'sponsorName', 'loggedInUser', 'stashed', 'autoConnect', 'token'));
    }

    /**
     * Stash registration details temporarily (15 mins) for cross-app Web3 handshake (e.g. SafePal DApp browser)
     */
    public function stashRegistrationState(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sponsor_id' => 'required',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $token = Str::random(32);
        $stashData = [
            'sponsor_id' => trim($request->sponsor_id),
            'name' => trim($request->name),
            'email' => trim($request->email),
            'password' => $request->password,
        ];

        try {
            Cache::store('file')->put('reg_stash_' . $token, $stashData, now()->addMinutes(15));
        } catch (\Throwable $e) {
            Cache::put('reg_stash_' . $token, $stashData, now()->addMinutes(15));
        }

        return response()->json([
            'status' => true,
            'token' => $token,
        ]);
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
     * Preview the eligible payee address and amount ($15 USDT) for a new registration
     */
    public function getRegistrationPayee(Request $request)
    {
        $sponsorId = $request->input('sponsor_id');
        $slotNumber = 1;

        $slot = Slot::where('slot_number', $slotNumber)->firstOrFail();
        $sponsor = User::where('member_id', $sponsorId)->first();
        if (!$sponsor || !$sponsor->is_paid) {
            $sponsor = User::where('member_id', 'COMPANY01')->firstOrFail();
        }

        $routing = $this->paymentRouteService->resolvePayeeForNewRegistration($sponsor->member_id, $slotNumber);
        $payeeUser = $routing['payee_user'] ?? null;
        $payeeWallet = $routing['wallet_address'];

        return response()->json([
            'status' => true,
            'slot_number' => 1,
            'slot_name' => $slot->name,
            'amount' => (float) $routing['amount'],
            'amount_wei' => bcmul((string) $routing['amount'], '1000000000000000000'),
            'payee_name' => $payeeUser ? $payeeUser->name : 'Eligible Beneficiary',
            'payee_member_id' => $payeeUser ? $payeeUser->member_id : 'COMPANY01',
            'payee_address' => $payeeWallet,
            'payee_masked_address' => maskAddress($payeeWallet),
            'usdt_contract' => env('USDT_CONTRACT_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'),
            'chain_id' => (int) env('BSC_CHAIN_ID', 56),
            'rpc_url' => env('BSC_RPC_URL', 'https://bsc-dataseed.binance.org'),
        ]);
    }

    public function register(Request $request)
    {
        $alreadyLoggedIn = Auth::guard('web')->user() ?? Auth::guard('member')->user();

        $validator = Validator::make($request->all(), [
            'sponsor_id' => 'required|exists:users,member_id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
            'wallet_address' => [
                'required',
                'regex:/^0x[a-fA-F0-9]{40}$/',
            ],
            'tx_hash' => 'required|string',
        ], [
            'wallet_address.required' => 'Please connect your SafePal or Web3 wallet to complete registration.',
            'wallet_address.regex' => 'Please connect a valid BSC (BEP-20) wallet address starting with 0x (42 characters).',
            'tx_hash.required' => 'Payment authorization required. Please approve the $15 USDT transfer in your wallet.',
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

        // Resolve designated payee for this registration in Slot 1
        $slot1 = Slot::where('slot_number', 1)->firstOrFail();
        $routing = $this->paymentRouteService->resolvePayeeForNewRegistration($sponsor->member_id, 1);
        $payeeAddress = $routing['wallet_address'];
        $expectedAmount = (float) $routing['amount'];
        $providedHash = trim($request->input('tx_hash'));

        // Verify on-chain transfer on BNB Smart Chain
        $verification = $this->blockchainService->verifyTransfer($walletAddress, $payeeAddress, $expectedAmount, $providedHash);

        if (!$verification['status']) {
            return ($request->expectsJson() || $request->ajax())
                ? response()->json(['status' => false, 'message' => $verification['message'] ?? 'Transaction could not be verified on BNB Smart Chain. Account was not created.'], 400)
                : back()->withErrors(['tx_hash' => $verification['message']])->withInput();
        }

        // Atomic registration: Create user directly as active paid, place in tree, and distribute commission
        $user = DB::transaction(function () use ($request, $sponsor, $walletAddress, $slot1, $verification) {
            $memberId = generateMemberId();

            $newUser = User::create([
                'member_id' => $memberId,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'sponsor_id' => $sponsor->member_id,
                'role_id' => 2,
                'is_paid' => true, // 100% PAID UPON REGISTRATION!
                'paid_at' => now(),
                'current_slot_id' => 1,
                'wallet_address' => $walletAddress,
            ]);

            // Place in 3-Matrix working tree
            $this->treePlacementService->placeInWorkingTree($newUser, $sponsor);

            // Process slot activation & distribute 15 USDT to payee
            $this->matrixIncomeService->processSlotActivation($newUser, $slot1, $verification);

            return $newUser;
        });

        $memberId = $user->member_id;

        // Send Welcome Email
        try {
            Mail::send('emails.welcome', ['user' => $user, 'sponsor' => $sponsor], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Welcome to NEVORA - Account Activated');
            });
        } catch (\Throwable $th) {
            Log::warning("Welcome email sending skipped/failed for {$user->email}: " . $th->getMessage());
        }

        // Condition 1: If a user was ALREADY logged in on this device (e.g. sponsor registering a direct), keep them logged in!
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
                    'tx_hash' => $verification['tx_hash'],
                    'message' => "Member {$user->name} (ID: {$memberId}) has been successfully registered and activated in Slot 1 under Sponsor {$sponsor->member_id}!",
                    'redirect' => route('member.network.directs')
                ]);
            }

            return redirect()->route('member.network.directs')->with('success', "Member {$user->name} (ID: {$memberId}) successfully registered and activated in Slot 1 under {$sponsor->member_id}!");
        }

        // Condition 2: No user was logged in (brand new visitor), log in directly!
        Auth::guard('web')->login($user);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'is_already_logged_in' => false,
                'is_existing' => false,
                'message' => 'Registration and Slot 1 Activation successful! Your Member ID is ' . $memberId . '.',
                'member_id' => $memberId,
                'tx_hash' => $verification['tx_hash'],
                'redirect' => route('member.dashboard')
            ]);
        }

        return redirect()->route('member.dashboard')->with('success', 'Registration and Slot 1 Activation successful! Your Member ID is ' . $memberId . '.');
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
