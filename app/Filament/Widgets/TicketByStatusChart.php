<?php

namespace App\Filament\Widgets;

use App\Models\Division;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketByStatusChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Status Tiket per Bidang';

    protected ?string $maxHeight = '300px';

    protected string $color = 'info';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $divisions = Division::with(['clients.tickets'])->get();

        $statuses = [
            'open' => ['label' => 'Open', 'color' => 'rgba(245, 158, 11, 0.8)'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'rgba(14, 165, 233, 0.8)'],
            'resolved' => ['label' => 'Resolved', 'color' => 'rgba(34, 197, 94, 0.8)'],
            'closed' => ['label' => 'Closed', 'color' => 'rgba(239, 68, 68, 0.8)'],
        ];

        $labels = $divisions->pluck('name')->toArray();
        $datasets = [];

        foreach ($statuses as $statusKey => $config) {
            $data = [];

            foreach ($divisions as $division) {
                $count = Ticket::whereHas('client', fn ($q) => $q->where('division_id', $division->id))
                    ->where('status', $statusKey)
                    ->count();
                $data[] = $count;
            }

            $datasets[] = [
                'label' => $config['label'],
                'data' => $data,
                'backgroundColor' => $config['color'],
                'borderRadius' => 4,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }
}
