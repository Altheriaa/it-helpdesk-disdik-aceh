<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'user_id', 'phone', 'message', 'status', 'sent_at'])]
class NotificationLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot the model and set created_at on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (NotificationLog $log): void {
            $log->created_at = $log->created_at ?? now();
        });
    }

    /**
     * Get the ticket this notification is about.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user this notification was sent to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
