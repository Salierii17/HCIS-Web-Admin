<?php

namespace App\Filament\Widgets\Reports;

use App\Models\AssignTraining;
use Filament\Widgets\BarChartWidget;

class TrainingCompletionWidget extends BarChartWidget
{
    protected static ?string $heading = 'Training Completion Status';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = AssignTraining::with('package')
            ->selectRaw('package_id, 
                SUM(CASE WHEN deadline < NOW() THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN deadline >= NOW() THEN 1 ELSE 0 END) as on_track')
            ->groupBy('package_id')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Overdue',
                    'data' => $data->pluck('overdue'),
                    'backgroundColor' => '#EF4444',
                ],
                [
                    'label' => 'On Track',
                    'data' => $data->pluck('on_track'),
                    'backgroundColor' => '#10B981',
                ],
            ],
            'labels' => $data->pluck('package.name'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ];
    }
}
