<?php

namespace App\Filament\Widgets;

use App\Models\Support;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class SupportPerformanceChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Kinerja IT Support';

    protected ?string $description = 'Jumlah tiket yang ditangani per IT Support';

    protected ?string $maxHeight = '300px';

    protected string $color = 'success';

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
        $supports = Support::with('user')->get();

        $labels = $supports->map(fn (Support $s) => $s->user->name ?? 'N/A')->toArray();

        $resolvedData = [];
        $inProgressData = [];
        $closedData = [];

        foreach ($supports as $support) {
            $resolvedData[] = Ticket::where('support_id', $support->id)
                ->where('status', 'resolved')
                ->count();

            $inProgressData[] = Ticket::where('support_id', $support->id)
                ->where('status', 'in_progress')
                ->count();

            $closedData[] = Ticket::where('support_id', $support->id)
                ->where('status', 'closed')
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Resolved',
                    'data' => $resolvedData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'In Progress',
                    'data' => $inProgressData,
                    'backgroundColor' => 'rgba(14, 165, 233, 0.8)',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Closed',
                    'data' => $closedData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
