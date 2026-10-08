<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

#[Fillable(['name', 'email', 'password', 'phone', 'address', 'role', 'agent_id', 'id_proof', 'photo', 'aadhar_card', 'pan_card', 'otp', 'otp_expires_at', 'is_phone_verified', 'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_account_type', 'additional_documents'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $appends = ['today_collection', 'target_progress'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function committees()
    {
        return $this->belongsToMany(Committee::class, 'committee_user')
            ->withPivot('joined_at', 'status')
            ->withTimestamps();
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function lotteries()
    {
        return $this->hasMany(Lottery::class, 'winner_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function agentTargets()
    {
        return $this->hasMany(AgentTarget::class, 'agent_id');
    }

    public function agentCollections()
    {
        return $this->hasMany(AgentCollection::class, 'agent_id');
    }

    public function getTodayCollectionAttribute()
    {
        $sum = $this->agentCollections()
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'approved')
            ->sum('amount_collected');
        return '₹' . number_format($sum);
    }

    public function getTargetProgressAttribute()
    {
        $target = $this->agentTargets()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->first();

        if (!$target || $target->target_value <= 0) {
            return 0;
        }

        if ($target->target_type === 'amount') {
            $collected = $this->agentCollections()
                ->whereBetween('created_at', [$target->start_date, $target->end_date])
                ->where('status', 'approved')
                ->sum('amount_collected');
        } else {
            $collected = $this->agentCollections()
                ->whereBetween('created_at', [$target->start_date, $target->end_date])
                ->where('status', 'approved')
                ->count();
        }

        return min(100, round(($collected / $target->target_value) * 100));
    }
}
