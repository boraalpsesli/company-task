<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    use HasRoles, HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'national_id',
        'birth_year',
        'company_id',
        'team_id',
        'otp',
        'otp_expires_at'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the company that the user belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the team that the user belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the transactions where this user is the sender.
     */
    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'sender_id');
    }

    /**
     * Get the transactions where this user is the receiver.
     */
    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }
}
