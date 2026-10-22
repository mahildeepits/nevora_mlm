<?php

use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

if (!function_exists('authUser')) {
    function authUser(): ?User
    {
        return Auth::guard('web')->user() ?? Auth::guard('member')->user();
    }
}

if (!function_exists('adminUser')) {
    function adminUser(): ?User
    {
        return Auth::guard('admin')->user();
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($amount, $currency = '$'): string
    {
        return $currency . ' ' . number_format((float) ($amount ?? 0), 2);
    }
}

if (!function_exists('getSetting')) {
    function getSetting(string $key, $default = null)
    {
        try {
            return Setting::where('key', $key)->value('value') ?? $default;
        } catch (\Throwable $th) {
            return $default;
        }
    }
}

if (!function_exists('maskAddress')) {
    function maskAddress(?string $address): string
    {
        if (!$address || strlen($address) < 10) {
            return $address ?? 'N/A';
        }
        return substr($address, 0, 6) . '...' . substr($address, -4);
    }
}

if (!function_exists('generateMemberId')) {
    function generateMemberId(): string
    {
        do {
            $id = 'PM' . mt_rand(100000, 999999);
        } while (User::where('member_id', $id)->exists());

        return $id;
    }
}
