<?php

namespace App\Models;

use App\Filament\Resources\TicketResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
     * Get formatted ticket number with prefix (e.g. TCK-0001).
     */
    public function getTicketNumberAttribute(): string
    {
        return 'TCK-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::updated(function (Ticket $ticket) {
            // Send real-time database notification to Pegawai when status changes
            if ($ticket->wasChanged('status')) {
                $clientUser = $ticket->client?->user;

                if ($clientUser) {
                    $statusText = match ($ticket->status) {
                        'open' => 'Baru / Open',
                        'in_progress' => 'Sedang Diproses (In Progress)',
                        'resolved' => 'Selesai (Resolved)',
                        'closed' => 'Ditutup (Closed)',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($ticket->status),
                    };

                    Notification::make()
                        ->title('Status Tiket #'.$ticket->ticket_number.' Diperbarui')
                        ->body('Status tiket Anda ("'.str($ticket->subject)->limit(35).'") kini diubah menjadi '.$statusText.'.')
                        ->icon('heroicon-o-arrow-path')
                        ->info()
                        ->actions([
                            Action::make('view')
                                ->label('Lihat Tiket')
                                ->url(TicketResource::getUrl('view', ['record' => $ticket])),
                        ])
                        ->sendToDatabase($clientUser);
                }
            }

            // Send notification when IT Support is assigned
            if ($ticket->wasChanged('support_id') && $ticket->support_id) {
                $clientUser = $ticket->client?->user;
                $supportName = $ticket->support?->user?->name ?? 'IT Support';

                if ($clientUser) {
                    Notification::make()
                        ->title('Tiket Ditangani Petugas IT')
                        ->body('Tiket #'.$ticket->ticket_number.' Anda kini sedang ditangani oleh '.$supportName.'.')
                        ->icon('heroicon-o-user-check')
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->label('Lihat Tiket')
                                ->url(TicketResource::getUrl('view', ['record' => $ticket])),
                        ])
                        ->sendToDatabase($clientUser);
                }
            }
        });
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
