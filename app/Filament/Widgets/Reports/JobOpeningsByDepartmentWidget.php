<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Departments;
use Filament\Widgets\DoughnutChartWidget;

class JobOpeningsByDepartmentWidget extends DoughnutChartWidget
{
    protected static ?string $heading = 'Open Positions by Department';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Departments::withCount(['jobOpenings' => function($query) {
                $query->where('Status', 'Opened');
            }])
            ->having('job_openings_count', '>', 0)
            ->orderBy('job_openings_count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('job_openings_count'),
                    'backgroundColor' => [
                        '#6366F1', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981'
                    ],
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => $data->pluck('DepartmentName'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => function($context) {
                            return "{$context['label']}: {$context['raw']} positions";
                        }
                    ]
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
