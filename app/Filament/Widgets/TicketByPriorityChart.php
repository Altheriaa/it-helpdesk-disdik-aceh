<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class TicketByPriorityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Distribusi Prioritas';

    protected ?string $maxHeight = '300px';

    protected string $color = 'warning';

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $user = auth()->user();
        $baseQuery = Ticket::query();

        if ($user?->hasRole('it_support')) {
            $baseQuery->where(function (Builder $q) use ($user) {
                $q->where('support_id', $user->support?->id)
                    ->orWhereNull('support_id');
            });
        } elseif ($user?->hasRole('pegawai')) {
            $baseQuery->whereHas('client', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        $priorities = [
            'low' => ['label' => 'Rendah', 'color' => 'rgba(156, 163, 175, 0.8)'],
            'medium' => ['label' => 'Sedang', 'color' => 'rgba(245, 158, 11, 0.8)'],
            'high' => ['label' => 'Tinggi', 'color' => 'rgba(239, 68, 68, 0.8)'],
            'critical' => ['label' => 'Kritis', 'color' => 'rgba(59, 130, 246, 0.8)'],
        ];

        $data = [];
        $labels = [];
        $colors = [];

        foreach ($priorities as $key => $config) {
            $count = (clone $baseQuery)->where('priority', $key)->count();
            $data[] = $count;
            $labels[] = $config['label'];
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
