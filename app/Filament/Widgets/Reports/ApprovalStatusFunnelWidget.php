<?php

namespace App\Filament\Widgets\Reports;

use App\Models\AttendanceApproval;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\BarChartWidget;

class ApprovalStatusFunnelWidget extends BarChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Approval Status (Last 30 Days)';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = AttendanceApproval::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'datasets' => [
                [
                    'label' => 'Approval Requests',
                    'data' => [
                        $data['pending'] ?? 0,
                        $data['approved'] ?? 0,
                        $data['rejected'] ?? 0,
                    ],
                    'backgroundColor' => ['#F59E0B', '#10B981', '#EF4444'],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['Pending', 'Approved', 'Rejected'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'datalabels' => [
                    'anchor' => 'end',
                    'align' => 'top',
                ],
            ],
        ];
    }
}
