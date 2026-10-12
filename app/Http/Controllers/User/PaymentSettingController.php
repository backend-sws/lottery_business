<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentSettingController extends Controller
{
    public function show()
    {
        $setting = PaymentSetting::latest()->first();
        if (!$setting) {
            $setting = PaymentSetting::create([
                'qr_code' => null,
                'admin_phone' => '+919999999999'
            ]);
        }

        $qrCodeUrl = null;
        if ($setting->qr_code) {
            $cacheBuster = $setting->updated_at ? $setting->updated_at->timestamp : time();
            $qrCodeUrl = url('/api/payment-setting/qr-code') . '?v=' . $cacheBuster;
        }

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'qr_code' => $qrCodeUrl,
                'admin_phone' => $setting->admin_phone
            ]
        ]);
    }

    public function qrCodeImage()
    {
        $setting = PaymentSetting::latest()->first();
        if (!$setting || !$setting->qr_code) {
            return response()->json(['message' => 'No QR code uploaded'], 404);
        }

        if (Storage::disk('public')->exists($setting->qr_code)) {
            return Storage::disk('public')->response($setting->qr_code);
        }

        if (Storage::disk('local')->exists($setting->qr_code)) {
            return Storage::disk('local')->response($setting->qr_code);
        }

        $publicPath = public_path('storage/' . $setting->qr_code);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        return response()->json(['message' => 'QR image file not found'], 404);
    }
}
