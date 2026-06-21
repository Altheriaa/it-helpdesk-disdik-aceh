<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'reply_id', 'file_path', 'file_name', 'file_size'])]
class File extends Model
{
    use HasFactory;

    /**
     * Get the ticket this file is attached to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the reply this file is attached to.
     */
    public function reply(): BelongsTo
    {
        return $this->belongsTo(Reply::class);
    }
}
