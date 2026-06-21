<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['client_id', 'support_id', 'subject', 'description', 'priority', 'status'])]
class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => 'string',
            'status' => 'string',
        ];
    }

    /**
     * Get the client (pegawai) who submitted this ticket.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the support (IT Support) assigned to this ticket.
     */
    public function support(): BelongsTo
    {
        return $this->belongsTo(Support::class);
    }

    /**
     * Get all replies on this ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * Get all files attached to this ticket.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
