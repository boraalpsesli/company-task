<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_id'
    ];

    /**
     * Get the company that owns the team.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the users in the team.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the transactions for the team.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
