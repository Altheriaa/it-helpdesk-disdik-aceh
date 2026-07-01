<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Client;
use App\Models\Support;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Tiket';

    protected static ?string $navigationLabel = 'Tiket';

    protected static ?string $modelLabel = 'Tiket';

    protected static ?string $pluralModelLabel = 'Tiket';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $isAdmin = $user?->hasRole('admin');
        $isPegawai = $user?->hasRole('pegawai');
        $isItSupport = $user?->hasRole('it_support');

        return $schema
            ->schema([
                Section::make('Detail Tiket')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Pegawai')
                            ->relationship('client', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Client $record) => $record->user->name.' — '.$record->division->name)
                            ->searchable(['id'])
                            ->preload()
                            ->required()
                            ->visible($isAdmin || $isItSupport)
                            ->default(fn () => $isPegawai ? $user?->client?->id : null),

                        Forms\Components\Hidden::make('client_id')
                            ->default(fn () => $user?->client?->id)
                            ->visible($isPegawai),

                        Forms\Components\TextInput::make('subject')
                            ->label('Subjek')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options(fn () => $isPegawai
                                ? ['low' => 'Rendah', 'medium' => 'Sedang']
                                : ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis']
                            )
                            ->default('medium')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(function (?Ticket $record) {
                                if (! $record) {
                                    return ['open' => 'Open'];
                                }
                                $status = $record->status;
                                if ($status === 'open') {
                                    return ['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed (Batal)'];
                                }
                                if ($status === 'in_progress') {
                                    return ['in_progress' => 'In Progress', 'resolved' => 'Resolved'];
                                }
                                if ($status === 'resolved') {
                                    return ['resolved' => 'Resolved', 'closed' => 'Closed', 'in_progress' => 'In Progress (Re-open)'];
                                }
                                if ($status === 'closed') {
                                    return ['closed' => 'Closed'];
                                }

                                return ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];
                            })
                            ->default('open')
                            ->required()
                            ->visible(fn (?Ticket $record) => $record !== null),

                        Forms\Components\Select::make('support_id')
                            ->label('Assign IT Support')
                            ->relationship('support', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Support $record) => $record->user->name)
                            ->searchable(['id'])
                            ->preload()
                            ->nullable()
                            ->visible($isAdmin || $isItSupport),
                    ])->columns(2),

                Section::make('Lampiran')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Upload File')
                            ->multiple()
                            ->directory('tickets')
                            ->disk('public')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->dehydrated(false),
                    ])
                    ->visible(fn (?Ticket $record) => $record === null)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Pegawai')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.division.name')
                    ->label('Bidang')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'critical' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'open' => 'gray',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('support.user.name')
                    ->label('IT Support')
                    ->default('Belum diassign')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                    ]),

                Tables\Filters\TrashedFilter::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'it_support'])),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasRole('admin')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    /**
     * Scope tickets based on user role.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            return $query;
        }

        if ($user?->hasRole('it_support')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('support_id', $user->support?->id)
                    ->orWhereNull('support_id');
            });
        }

        if ($user?->hasRole('pegawai')) {
            return $query->whereHas('client', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            $count = Ticket::where('status', 'open')->count();
        } elseif ($user?->hasRole('it_support')) {
            $count = Ticket::where(function (Builder $q) use ($user) {
                $q->where('support_id', $user->support?->id)
                    ->orWhereNull('support_id');
            })
                ->whereIn('status', ['open', 'in_progress'])
                ->count();
        } else {
            $count = Ticket::whereHas('client', fn (Builder $q) => $q->where('user_id', $user?->id))
                ->whereIn('status', ['open', 'in_progress'])
                ->count();
        }

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
