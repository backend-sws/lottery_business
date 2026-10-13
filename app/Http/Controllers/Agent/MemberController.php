<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\User;

class MemberController extends Controller
{
    // 🔍 Search Member for Collection
    public function search(Request $request)
    {
        $rawQuery = trim((string)$request->input('query', ''));

        if ($rawQuery === '') {
            return ApiResponse::success([], 'Empty search query');
        }

        // Clean digits for phone number search
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawQuery);
        $last10 = (strlen($cleanDigits) >= 10) ? substr($cleanDigits, -10) : null;

        // Extract numeric ID if query is like "#MEM-4", "MEM-4", "#4", "id 4", or "4"
        $extractedId = null;
        if (preg_match('/^(?:#|mem|#mem|cust|client|customer|id)?[-\s:#]*(\d+)$/i', $rawQuery, $matches)) {
            $extractedId = (int)$matches[1];
        } elseif (ctype_digit($rawQuery)) {
            $extractedId = (int)$rawQuery;
        }

        // Find member with role 'member' (or client/user, excluding agent/admin)
        $members = User::where(function($rq) {
                $rq->where('role', 'member')
                  ->orWhere('role', 'client')
                  ->orWhere('role', 'customer')
                  ->orWhereHas('roles', function($sq) {
                      $sq->whereIn('name', ['member', 'client', 'customer']);
                  })
                  ->orWhere(function($sub) {
                      $sub->whereNotIn('role', ['agent', 'Super Admin', 'admin']);
                  });
            })
            ->where(function($q) use ($rawQuery, $cleanDigits, $last10, $extractedId) {
                // 1. By ID (highest specificity)
                if ($extractedId !== null) {
                    $q->where('id', $extractedId);
                }

                // 2. By Name & Email
                if ($extractedId !== null) {
                    $q->orWhere('name', 'like', "%{$rawQuery}%")
                      ->orWhere('email', 'like', "%{$rawQuery}%");
                } else {
                    $q->where(function($subQ) use ($rawQuery) {
                        $subQ->where('name', 'like', "%{$rawQuery}%")
                             ->orWhere('email', 'like', "%{$rawQuery}%");
                    });
                }

                // 3. By Phone
                if (!empty($cleanDigits) && strlen($cleanDigits) >= 3) {
                    $q->orWhere('phone', 'like', "%{$cleanDigits}%");

                    if (!empty($last10)) {
                        $q->orWhere('phone', 'like', "%{$last10}%");
                    }

                    // Normalized DB phone match (strips '+', '-', ' ', '(', ')')
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') LIKE ?", ["%{$cleanDigits}%"]);
                    if (!empty($last10)) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') LIKE ?", ["%{$last10}%"]);
                    }
                }
            })
            ->with([
                'committees' => function($q) {
                    $q->where('committee_user.status', 'active');
                },
                'installments' => function($q) {
                    $q->where('status', 'pending')->with('committee');
                },
                'loans' => function($q) {
                    $q->where('status', 'active')->with(['installments' => function($sq) {
                        $sq->where('status', 'pending');
                    }]);
                }
            ])
            ->take(30)
            ->get();

        return ApiResponse::success($members, 'Members fetched');
    }
}
