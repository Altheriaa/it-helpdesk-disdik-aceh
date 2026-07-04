<?php

namespace App\Filament\Widgets;

use App\Models\Division;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketByDivisionChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Tiket per Bidang';

    protected ?string $maxHeight = '300px';

    protected string $color = 'danger';

    public ?string $filter = 'all';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'it_support']) ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua',
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $divisions = Division::all();
        $labels = $divisions->pluck('name')->toArray();
        $data = [];

        $colors = [
            'rgba(99, 102, 241, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(14, 165, 233, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(107, 114, 128, 0.8)',
        ];

        foreach ($divisions as $index => $division) {
            $query = Ticket::whereHas('client', fn ($q) => $q->where('division_id', $division->id));

            if ($this->filter && $this->filter !== 'all') {
                $query->where('status', $this->filter);
            }

            $data[] = $query->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Tiket',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($divisions)),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
