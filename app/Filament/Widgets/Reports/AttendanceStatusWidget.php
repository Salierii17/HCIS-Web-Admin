<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Filament\Widgets\ChartWidget;

class AttendanceStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Attendance Status (Last 30 Days)';

    protected function getData(): array
    {
        $statuses = AttendanceStatus::all();
        $data = Attendance::where('date', '>=', now()->subDays(30))
            ->groupBy('status_id')
            ->selectRaw('status_id, count(*) as count')
            ->pluck('count', 'status_id');

        return [
            'datasets' => [
                [
                    'label' => 'Attendance Status',
                    'data' => $statuses->map(fn ($status) => $data[$status->id] ?? 0),
                    'backgroundColor' => ['#36A2EB', '#FF6384', '#FFCD56'],
                ],
            ],
            'labels' => $statuses->pluck('status'),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
