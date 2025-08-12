<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\PieChartWidget;

class AttendanceStatusWidget extends PieChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Attendance Status Distribution';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = AttendanceStatus::all();
        $data = Attendance::where('date', '>=', now()->subDays(30))
            ->selectRaw('status_id, count(*) as count')
            ->groupBy('status_id')
            ->pluck('count', 'status_id');

        return [
            'datasets' => [
                [
                    'data' => $statuses->map(fn ($status) => $data[$status->id] ?? 0),
                    'backgroundColor' => ['#3B82F6', '#EF4444', '#F59E0B'],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $statuses->pluck('status'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($context) {
                            $total = array_sum($context['chart']->data->datasets[0]->data);
                            $percentage = round(($context['raw'] / $total) * 100, 2);

                            return "{$context['label']}: {$context['raw']} ({$percentage}%)";
                        },
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
