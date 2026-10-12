<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'duration_months',
        'payment_frequency',
        'purpose',
        'status',
        'admin_remarks',
        'approved_loan_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'duration_months' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedLoan()
    {
        return $this->belongsTo(Loan::class, 'approved_loan_id');
    }
}
