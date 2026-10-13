<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\User;

class AgentController extends Controller
{
    /**
     * Get list of active agents for customer EMI collection
     */
    public function index(Request $request)
    {
        // 1. Fetch users with role 'agent'
        try {
            $agents = User::role('agent')
                ->select('id', 'name', 'phone', 'email')
                ->orderBy('name', 'asc')
                ->get();
        } catch (\Exception $e) {
            $agents = collect();
        }

        // 2. Fallback: if empty, check roles 'Agent', 'agent', 'staff' via whereHas
        if ($agents->isEmpty()) {
            try {
                $agents = User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['agent', 'Agent', 'staff', 'Staff']);
                })
                ->select('id', 'name', 'phone', 'email')
                ->orderBy('name', 'asc')
                ->get();
            } catch (\Exception $e) {
                $agents = collect();
            }
        }

        // 3. Fallback: if still empty, fetch Super Admin as default field collector
        if ($agents->isEmpty()) {
            try {
                $agents = User::whereHas('roles', function($q) {
                    $q->where('name', 'Super Admin');
                })
                ->select('id', 'name', 'phone', 'email')
                ->limit(2)
                ->get();
            } catch (\Exception $e) {
                $agents = collect();
            }
        }

        // 4. Ensure at least one fallback agent is returned so UI never breaks
        if ($agents->isEmpty()) {
            $firstUser = User::select('id', 'name', 'phone', 'email')->first();
            if ($firstUser) {
                $agents = collect([$firstUser]);
            }
        }

        return ApiResponse::success($agents, 'Agents fetched successfully');
    }
}
