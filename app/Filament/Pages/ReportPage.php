<?php

namespace App\Filament\Pages;

use App\Models\Division;
use App\Models\Support;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ReportPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Rekap Laporan';

    protected static ?string $title = 'Rekap Laporan Tiket';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.report-page';

    public ?string $date_from = null;

    public ?string $date_until = null;

    public ?string $division_id = null;

    public ?string $status = null;

    public ?string $support_id = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),

                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),

                        Forms\Components\Select::make('division_id')
                            ->label('Bidang')
                            ->options(Division::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->nullable(),

                        Forms\Components\Select::make('support_id')
                            ->label('IT Support')
                            ->options(Support::with('user')->get()->pluck('user.name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(5),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Pegawai'),

                Tables\Columns\TextColumn::make('client.division.name')
                    ->label('Bidang'),

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

                Tables\Columns\TextColumn::make('support.user.name')
                    ->label('IT Support')
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Actions\Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->action(fn () => $this->exportPdf()),
            ]);
    }

    protected function getFilteredQuery(): Builder
    {
        $query = Ticket::query()->with(['client.user', 'client.division', 'support.user']);

        if ($this->date_from) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->date_from));
        }

        if ($this->date_until) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->date_until));
        }

        if ($this->division_id) {
            $query->whereHas('client', fn (Builder $q) => $q->where('division_id', $this->division_id));
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->support_id) {
            $query->where('support_id', $this->support_id);
        }

        return $query;
    }

    public function exportPdf()
    {
        $tickets = $this->getFilteredQuery()->get();

        $pdf = Pdf::loadView('reports.tickets-pdf', [
            'tickets' => $tickets,
            'dateFrom' => $this->date_from,
            'dateUntil' => $this->date_until,
            'generatedAt' => now()->format('d M Y H:i'),
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'laporan-tiket-'.now()->format('Y-m-d').'.pdf'
        );
    }
}
