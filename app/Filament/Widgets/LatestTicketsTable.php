<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTicketsTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = Ticket::query()->with(['client.user', 'client.division', 'support.user']);

        if ($user?->hasRole('it_support')) {
            $query->where('support_id', $user->support?->id);
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
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'open' => 'gray', 'in_progress' => 'warning',
                        'resolved' => 'success', 'closed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i'),
            ]);
    }
}
