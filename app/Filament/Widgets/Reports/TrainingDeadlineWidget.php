<?php

namespace App\Filament\Widgets\Reports;

use App\Models\AssignTraining;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TrainingDeadlineWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Upcoming Training Deadlines';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = Trend::model(AssignTraining::class)
            ->between(
                start: now(),
                end: now()->addDays(30),
            )
            ->dateColumn('deadline')
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Deadlines',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#4BC0C0',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.3,
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
