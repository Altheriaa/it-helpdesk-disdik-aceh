<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TicketTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Tren Tiket';

    protected ?string $maxHeight = '300px';

    protected string $color = 'primary';

    public ?string $filter = '6_months';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '30_days' => '30 Hari Terakhir',
            '3_months' => '3 Bulan Terakhir',
            '6_months' => '6 Bulan Terakhir',
        ];
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

        $periods = $this->buildPeriods();

        $statuses = [
            'open' => ['label' => 'Open', 'color' => 'rgba(245, 158, 11, 0.8)'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'rgba(14, 165, 233, 0.8)'],
            'resolved' => ['label' => 'Resolved', 'color' => 'rgba(34, 197, 94, 0.8)'],
            'closed' => ['label' => 'Closed', 'color' => 'rgba(239, 68, 68, 0.8)'],
        ];

        $datasets = [];

        foreach ($statuses as $statusKey => $config) {
            $data = [];

            foreach ($periods as $period) {
                $data[] = (clone $baseQuery)
                    ->where('status', $statusKey)
                    ->whereBetween('created_at', [$period['start'], $period['end']])
                    ->count();
            }

            $datasets[] = [
                'label' => $config['label'],
                'data' => $data,
                'borderColor' => $config['color'],
                'backgroundColor' => str_replace('0.8', '0.1', $config['color']),
                'tension' => 0.3,
                'fill' => true,
            ];
        }

        $labels = array_map(fn (array $p) => $p['label'], $periods);

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    /**
     * Build date periods based on the active filter.
     *
     * @return array<int, array{label: string, start: Carbon, end: Carbon}>
     */
    private function buildPeriods(): array
    {
        return match ($this->filter) {
            '30_days' => $this->buildDailyPeriods(30),
            '3_months' => $this->buildMonthlyPeriods(3),
            default => $this->buildMonthlyPeriods(6),
        };
    }

    /**
     * @return array<int, array{label: string, start: Carbon, end: Carbon}>
     */
    private function buildMonthlyPeriods(int $months): array
    {
        $periods = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();

            $periods[] = [
                'label' => $start->translatedFormat('M Y'),
                'start' => $start,
                'end' => $end,
            ];
        }

        return $periods;
    }

    /**
     * @return array<int, array{label: string, start: Carbon, end: Carbon}>
     */
    private function buildDailyPeriods(int $days): array
    {
        $periods = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $start = Carbon::today()->subDays($i)->startOfDay();
            $end = Carbon::today()->subDays($i)->endOfDay();

            $periods[] = [
                'label' => $start->format('d/m'),
                'start' => $start,
                'end' => $end,
            ];
        }

        return $periods;
    }
}
