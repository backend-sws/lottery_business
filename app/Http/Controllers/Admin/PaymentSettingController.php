<?php

namespace App\Http\Controllers\Admin;

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

    public function update(Request $request)
    {
        $request->validate([
            'admin_phone' => 'nullable|string',
            'qr_code' => 'nullable|image|max:10240'
        ]);

        $setting = PaymentSetting::latest()->first();
        if (!$setting) {
            $setting = new PaymentSetting();
        }

        if ($request->hasFile('qr_code')) {
            if ($setting->qr_code && Storage::disk('public')->exists($setting->qr_code)) {
                Storage::disk('public')->delete($setting->qr_code);
            }
            $path = $request->file('qr_code')->store('payment', 'public');
            $setting->qr_code = $path;
        }

        if ($request->has('admin_phone') && $request->filled('admin_phone')) {
            $setting->admin_phone = $request->admin_phone;
        }

        $setting->save();

        $qrCodeUrl = null;
        if ($setting->qr_code) {
            $cacheBuster = $setting->updated_at ? $setting->updated_at->timestamp : time();
            $qrCodeUrl = url('/api/payment-setting/qr-code') . '?v=' . $cacheBuster;
        }

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Payment Settings Updated',
            'data' => [
                'qr_code' => $qrCodeUrl,
                'admin_phone' => $setting->admin_phone
            ]
        ]);
    }
}
