<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Division;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected string $view = 'filament.resources.ticket-resource.pages.list-tickets';

    public string $viewMode = 'kanban';

    public string $search = '';

    public string $priorityFilter = '';

    public string $divisionFilter = '';

    public string $periodFilter = 'today';

    public ?int $lastTicketId = null;

    public function setMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function moveStatus(int $ticketId, string $newStatus): void
    {
        $ticket = TicketResource::getEloquentQuery()->find($ticketId);

        if (! $ticket) {
            return;
        }

        $user = auth()->user();

        // Update status and support_id if moving to in_progress
        $data = ['status' => $newStatus];
        if ($newStatus === 'in_progress' && $user?->hasRole('it_support') && ! $ticket->support_id) {
            $data['support_id'] = $user->support?->id;
        }

        $ticket->update($data);

        Notification::make()
            ->title('Status Tiket Diperbarui')
            ->body('Tiket #'.$ticket->id.' berhasil dipindahkan ke status '.ucfirst(str_replace('_', ' ', $newStatus)).'.')
            ->success()
            ->send();
    }

    public function assignToMe(int $ticketId): void
    {
        $ticket = TicketResource::getEloquentQuery()->find($ticketId);
        $user = auth()->user();

        if (! $ticket || ! $user?->hasRole('it_support')) {
            return;
        }

        $ticket->update([
            'support_id' => $user->support?->id,
            'status' => 'in_progress',
        ]);

        Notification::make()
            ->title('Tiket Berhasil Di-assign')
            ->body('Anda sekarang menangani Tiket #'.$ticket->id.'.')
            ->success()
            ->send();
    }

    public function getKanbanTickets(): array
    {
        $maxId = TicketResource::getEloquentQuery()->max('id') ?? 0;

        if ($this->lastTicketId !== null && $maxId > $this->lastTicketId) {
            $newTickets = TicketResource::getEloquentQuery()
                ->with(['client.user'])
                ->where('id', '>', $this->lastTicketId)
                ->get();

            foreach ($newTickets as $ticket) {
                $clientName = $ticket->client->user->name ?? 'Pegawai';

                Notification::make()
                    ->title('Tiket Baru Masuk!')
                    ->body('Tiket #'.$ticket->id.' dari '.$clientName.': '.$ticket->subject)
                    ->icon('heroicon-o-ticket')
                    ->warning()
                    ->actions([
                        Actions\Action::make('view')
                            ->label('Lihat Tiket')
                            ->url(TicketResource::getUrl('view', ['record' => $ticket])),
                    ])
                    ->send();
            }
        }

        $this->lastTicketId = $maxId;

        $query = TicketResource::getEloquentQuery()
            ->with(['client.user', 'client.division', 'support.user']);

        if (filled($this->search)) {
            $query->where(function (Builder $q) {
                $q->where('subject', 'like', '%'.$this->search.'%')
                    ->orWhere('id', 'like', '%'.$this->search.'%')
                    ->orWhereHas('client.user', fn (Builder $uq) => $uq->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        if (filled($this->priorityFilter)) {
            $query->where('priority', $this->priorityFilter);
        }

        if (filled($this->divisionFilter)) {
            $query->whereHas('client', fn (Builder $q) => $q->where('division_id', $this->divisionFilter));
        }

        if ($this->periodFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->periodFilter === 'week') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        }

        $allTickets = $query->latest()->get();

        return [
            'open' => $allTickets->where('status', 'open'),
            'in_progress' => $allTickets->where('status', 'in_progress'),
            'resolved' => $allTickets->whereIn('status', ['resolved', 'closed']),
        ];
    }

    public function getDivisionsProperty()
    {
        return Division::pluck('name', 'id');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Tiket Baru')
                ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'pegawai'])),
        ];
    }
}
