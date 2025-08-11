<?php

namespace App\Filament\Widgets\Reports;

use App\Models\JobCandidates;
use Filament\Widgets\BarChartWidget;

class RecruitmentFunnelWidget extends BarChartWidget
{
    protected static ?string $heading = 'Recruitment Funnel';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $stages = ['New', 'Screening', 'Interviewing', 'Offered', 'Hired'];
        $data = JobCandidates::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('CandidateStatus, count(*) as count')
            ->groupBy('CandidateStatus')
            ->pluck('count', 'CandidateStatus');

        return [
            'datasets' => [
                [
                    'label' => 'Candidates',
                    'data' => collect($stages)->map(fn ($stage) => $data[$stage] ?? 0),
                    'backgroundColor' => [
                        '#6366F1', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981',
                    ],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $stages,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($context) {
                            $total = JobCandidates::count();
                            $percentage = $total > 0 ? round(($context['raw'] / $total) * 100, 2) : 0;

                            return "{$context['label']}: {$context['raw']} ({$percentage}%)";
                        },
                    ],
                ],
            ],
        ];
    }
}
