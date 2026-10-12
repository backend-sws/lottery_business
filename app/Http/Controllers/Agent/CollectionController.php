<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Installment;
use App\Models\LoanInstallment;
use App\Models\AgentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    // 💵 Submit Cash Collection to Admin for Approval
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:committee,loan',
            'installment_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'payment_notes' => 'nullable|string',
            'payment_mode' => 'nullable|string|in:cash,online',
            'payment_method' => 'nullable|string|in:cash,online'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $agent = $request->user();
        $type = $request->type;
        $installmentId = $request->installment_id;

        DB::beginTransaction();

        try {
            $memberId = null;
            $installmentColumn = null;
            
            if ($type === 'committee') {
                $installment = Installment::where('id', $installmentId)->firstOrFail();

                if ($installment->status === 'paid') {
                    return ApiResponse::error("Installment is already paid", 400);
                }
                
                // Check if already submitted
                $existingCollection = AgentCollection::where('installment_id', $installmentId)
                    ->whereIn('status', ['pending', 'approved'])
                    ->first();
                if ($existingCollection) {
                    return ApiResponse::error("Collection for this installment is already submitted or approved.", 400);
                }

                $memberId = $installment->user_id;
                $installmentColumn = 'installment_id';

            } else {
                $installment = LoanInstallment::with('loan')->where('id', $installmentId)->firstOrFail();

                if ($installment->status === 'paid') {
                    return ApiResponse::error("Loan installment is already paid", 400);
                }
                
                // Check if already submitted
                $existingCollection = AgentCollection::where('loan_installment_id', $installmentId)
                    ->whereIn('status', ['pending', 'approved'])
                    ->first();
                if ($existingCollection) {
                    return ApiResponse::error("Collection for this loan installment is already submitted or approved.", 400);
                }

                $memberId = $installment->loan->user_id;
                $installmentColumn = 'loan_installment_id';
            }

            $paymentMode = $request->input('payment_mode', $request->input('payment_method', 'cash'));
            if (!in_array($paymentMode, ['cash', 'online'])) {
                $paymentMode = 'cash';
            }

            // Create AgentCollection Request matching actual DB schema
            AgentCollection::create([
                'agent_id' => $agent->id,
                'member_id' => $memberId,
                'collection_type' => $type,
                'amount_collected' => $request->amount,
                'payment_mode' => $paymentMode,
                'details' => $request->payment_notes,
                'status' => 'pending', // actual DB enum is pending, approved, rejected
                $installmentColumn => $installment->id,
                'collected_at' => now()
            ]);

            DB::commit();
            return ApiResponse::success(null, 'Submitted to Admin for Approval');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Submission failed: ' . $e->getMessage(), 500);
        }
    }

    // 📜 My Collections History
    public function history(Request $request)
    {
        $agentId = $request->user()->id;

        // Fetch from AgentCollection directly for history
        $collections = AgentCollection::with(['member', 'installment', 'loanInstallment'])
            ->where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'member_name' => $item->member->name ?? 'Unknown',
                    'amount' => $item->amount_collected,
                    'payment_mode' => $item->payment_mode ?? 'cash',
                    'status' => $item->status,
                    'type' => $item->collection_type === 'committee' ? 'Committee' : 'Loan EMI',
                    'date' => $item->created_at->format('Y-m-d H:i:s'),
                    'payment_notes' => $item->details
                ];
            });

        return ApiResponse::success($collections, 'Collection history fetched');
    }
}
