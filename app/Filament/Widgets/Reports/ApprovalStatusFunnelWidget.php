<?php

namespace App\Filament\Widgets\Reports;

use App\Models\AttendanceApproval;
use Filament\Widgets\ChartWidget;

class ApprovalStatusFunnelWidget extends ChartWidget
{
    protected static ?string $heading = 'Approval Requests (Last 30 Days)';

    protected function getData(): array
    {
        $statuses = ['pending', 'approved', 'rejected'];
        $data = AttendanceApproval::where('created_at', '>=', now()->subDays(30))
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status');

        return [
            'datasets' => [
                [
                    'label' => 'Request Status',
                    'data' => collect($statuses)->map(fn ($status) => $data[$status] ?? 0),
                ],
            ],
            'labels' => array_map('ucfirst', $statuses),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
