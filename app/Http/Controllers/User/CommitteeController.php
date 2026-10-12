<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Committee;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommitteeController extends Controller
{
    // 🏢 List all active committees
    public function index()
    {
        $committees = Committee::where('status', 'active')->get();
        return ApiResponse::success($committees, 'Committees fetched');
    }

    // 📄 View single committee details
    public function show($id)
    {
        $committee = Committee::findOrFail($id);
        return ApiResponse::success($committee, 'Committee details fetched');
    }

    // 🙋‍♂️ My Committees
    public function myCommittees(Request $request)
    {
        $committees = $request->user()->committees()->get();
        return ApiResponse::success($committees, 'My committees fetched');
    }

    // 🤝 Join a Committee
    public function join(Request $request, $id)
    {
        $user = $request->user();
        $committee = Committee::findOrFail($id);

        if ($committee->status !== 'active') {
            return ApiResponse::error('Committee is not active', 400);
        }

        if ($user->committees()->where('committee_id', $id)->exists()) {
            return ApiResponse::error('You are already a member of this committee', 400);
        }

        DB::beginTransaction();
        try {
            // Attach user to committee
            $user->committees()->attach($id, ['status' => 'active', 'joined_at' => now()]);

            // Generate Installments
            $amount = $committee->amount / $committee->duration; // Monthly installment amount
            $date = $committee->start_date ? Carbon::parse($committee->start_date) : Carbon::now();

            $installments = [];
            for ($i = 1; $i <= $committee->duration; $i++) {
                if ($committee->payment_frequency === 'monthly') {
                    $date->addMonth();
                } elseif ($committee->payment_frequency === 'weekly') {
                    $date->addWeek();
                } else {
                    $date->addDay(); // Daily
                }

                $installments[] = [
                    'user_id' => $user->id,
                    'committee_id' => $committee->id,
                    'amount' => round($amount, 2),
                    'due_date' => $date->format('Y-m-d'),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Installment::insert($installments);

            DB::commit();
            return ApiResponse::success(null, 'Successfully joined committee and installments generated');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to join committee: ' . $e->getMessage(), 500);
        }
    }
}
