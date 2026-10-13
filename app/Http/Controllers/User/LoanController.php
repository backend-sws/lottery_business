<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Loan;

class LoanController extends Controller
{
    // 💳 List User Loans
    public function index(Request $request)
    {
        $loans = Loan::where('user_id', $request->user()->id)
            ->withCount('installments')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return ApiResponse::success($loans, 'Loans fetched successfully');
    }

    // 📄 Single Loan Details
    public function show(Request $request, $id)
    {
        $loan = Loan::where('user_id', $request->user()->id)
            ->with(['installments' => function($q) {
                $q->orderBy('due_date', 'asc')->orderBy('id', 'asc');
            }])
            ->findOrFail($id);
            
        return ApiResponse::success($loan, 'Loan details fetched successfully');
    }
}
