<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCollection extends Model
{
    protected $fillable = [
        'agent_id',
        'member_id',
        'collection_type', // 'committee' or 'loan'
        'amount_collected',
        'payment_mode',
        'details',
        'status', // pending, approved, rejected
        'commission_percentage',
        'commission_amount',
        'commission_status',
        'installment_id',
        'loan_installment_id',
        'collected_at'
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function loanInstallment()
    {
        return $this->belongsTo(LoanInstallment::class);
    }
}
