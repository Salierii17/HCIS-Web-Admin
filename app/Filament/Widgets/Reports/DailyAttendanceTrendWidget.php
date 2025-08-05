<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class DailyAttendanceTrendWidget extends ChartWidget
{
    protected static ?string $heading = 'Daily Attendance Trend (Last 30 Days)';

    protected function getData(): array
    {

        $data = Trend::model(Attendance::class)
            ->between(
                start: now()->subDays(29),
                end: now(),
            )
            ->dateColumn('date')
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Attendance',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
