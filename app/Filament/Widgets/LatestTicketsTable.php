<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTicketsTable extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            return 'Tiket Terbaru';
        }

        if ($user?->hasRole('it_support')) {
            return 'Antrian Tiket Anda';
        }

        return 'Tiket Anda Terbaru';
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = Ticket::query()->with(['client.user', 'client.division', 'support.user']);

        if ($user?->hasRole('it_support')) {
            $query->where(function (Builder $q) use ($user) {
                $q->where('support_id', $user->support?->id)
                    ->orWhereNull('support_id');
            });
        } elseif ($user?->hasRole('pegawai')) {
            $query->whereHas('client', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        return $table
            ->query($query)
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#'),

                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Pegawai'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->limit(30),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'low' => 'Rendah', 'medium' => 'Sedang',
                        'high' => 'Tinggi', 'critical' => 'Kritis',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'low' => 'gray', 'medium' => 'warning',
                        'high' => 'danger', 'critical' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'open' => 'Open', 'in_progress' => 'In Progress',
                        'resolved' => 'Resolved', 'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'open' => 'gray', 'in_progress' => 'warning',
                        'resolved' => 'success', 'closed' => 'danger',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('support.user.name')
                    ->label('IT Support')
                    ->default('Belum diassign'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i'),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->url(fn (Ticket $record) => route('filament.admin.resources.tickets.view', $record)),
            ]);
    }
}
