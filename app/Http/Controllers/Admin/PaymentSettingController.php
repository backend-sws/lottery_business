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
         = PaymentSetting::latest()->first();
        if (!) {
             = PaymentSetting::create([
                'qr_code' => null,
                'admin_phone' => '+919999999999'
            ]);
        }

         = null;
        if (->qr_code) {
             = ->updated_at ? ->updated_at->timestamp : time();
             = url('/api/payment-setting/qr-code') . '?v=' . ;
        }

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'qr_code' => ,
                'admin_phone' => ->admin_phone
            ]
        ]);
    }

    public function update(Request )
    {
        ->validate([
            'admin_phone' => 'nullable|string',
            'qr_code' => 'nullable|image|max:10240'
        ]);

         = PaymentSetting::latest()->first();
        if (!) {
             = new PaymentSetting();
        }

        if (->hasFile('qr_code')) {
            if (->qr_code && Storage::disk('public')->exists(->qr_code)) {
                Storage::disk('public')->delete(->qr_code);
            }
             = ->file('qr_code')->store('payment', 'public');
            ->qr_code = ;
        }

        if (->has('admin_phone') && ->filled('admin_phone')) {
            ->admin_phone = ->admin_phone;
        }

        ->save();

         = null;
        if (->qr_code) {
             = ->updated_at ? ->updated_at->timestamp : time();
             = url('/api/payment-setting/qr-code') . '?v=' . ;
        }

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Payment Settings Updated',
            'data' => [
                'qr_code' => ,
                'admin_phone' => ->admin_phone
            ]
        ]);
    }
}
