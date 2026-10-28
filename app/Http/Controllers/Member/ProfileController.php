<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function index()
    {
        $user = authUser();
        return view('member.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = authUser();

        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|max:20|unique:users,mobile,' . $user->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
        ], [
            'mobile.required' => 'Mobile number is required.',
            'mobile.unique' => 'This mobile number is already registered to another account.',
        ]);

        $user->update($request->only([
            'name', 'mobile', 'address', 'city', 'state', 'country', 'pincode'
        ]));

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $user = authUser();

        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete('profile_images/' . $user->profile_image);
            }

            $file = $request->file('profile_image');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_images', $filename, 'public');

            $user->profile_image = $filename;
            $user->save();

            return back()->with('success', 'Profile image updated successfully.');
        }

        return back()->with('error', 'No image file uploaded.');
    }

    public function updatePassword(Request $request)
    {
        $user = authUser();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match our records.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }

    public function sendWalletOtp(Request $request)
    {
        $user = authUser();
        $res = $this->otpService->generateAndSend($user->email, 'wallet_binding');
        return response()->json($res);
    }

    public function updateWalletAddress(Request $request)
    {
        $user = authUser();

        $validator = Validator::make($request->all(), [
            'wallet_address' => [
                'required',
                'regex:/^0x[a-fA-F0-9]{40}$/',
            ],
            'otp' => 'required|string',
        ], [
            'wallet_address.regex' => 'Please enter a valid BSC (BEP-20) wallet address starting with 0x (42 characters).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify OTP
        if (!$this->otpService->verify($user->email, $request->otp, 'wallet_binding')) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP verification code.'])->withInput();
        }

        $address = strtolower(trim($request->wallet_address));

        // Ensure wallet address is unique across other users
        $exists = User::where('wallet_address', $address)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['wallet_address' => 'This wallet address is already linked to another account.'])->withInput();
        }

        $user->wallet_address = $address;
        $user->save();

        return back()->with('success', 'BEP-20 USDT wallet address linked successfully!');
    }

    /**
     * 1-Click Web3 Wallet Binding (Direct from SafePal / Web3Modal)
     */
    public function bindWeb3Wallet(Request $request)
    {
        $user = authUser();

        $validator = Validator::make($request->all(), [
            'wallet_address' => [
                'required',
                'regex:/^0x[a-fA-F0-9]{40}$/',
            ],
        ], [
            'wallet_address.regex' => 'Please provide a valid BEP-20 wallet address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $address = strtolower(trim($request->wallet_address));

        // Check if address is already registered to someone else
        $exists = User::where('wallet_address', $address)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'This wallet address is already bound to another member account.'
            ], 422);
        }

        $user->wallet_address = $address;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'SafePal wallet linked successfully!',
            'wallet_address' => $address,
            'masked_address' => maskAddress($address),
        ]);
    }
}
