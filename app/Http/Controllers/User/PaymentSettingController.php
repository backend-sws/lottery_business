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

    public function qrCodeImage()
    {
         = PaymentSetting::latest()->first();
        if (! || !->qr_code) {
            return response()->json(['message' => 'No QR code uploaded'], 404);
        }

        if (Storage::disk('public')->exists(->qr_code)) {
            return Storage::disk('public')->response(->qr_code);
        }

        if (Storage::disk('local')->exists(->qr_code)) {
            return Storage::disk('local')->response(->qr_code);
        }

         = public_path('storage/' . ->qr_code);
        if (file_exists()) {
            return response()->file();
        }

        return response()->json(['message' => 'QR image file not found'], 404);
    }
}
