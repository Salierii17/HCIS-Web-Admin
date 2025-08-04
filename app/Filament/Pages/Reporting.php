<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\View;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;

class Reporting extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static string $view = 'filament.pages.reporting';

    protected static ?int $navigationSort = 1;

    public function reportingInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state([])
            ->schema([
                Tabs::make('Modules')
                    ->tabs([

                        Tabs\Tab::make('Recruitment')
                            ->icon('heroicon-o-user-plus')
                            ->schema([
                                View::make('filament.reporting-tabs.recruitment'),
                            ]),

                        Tabs\Tab::make('Training')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                View::make('filament.reporting-tabs.training'),
                            ]),
                        Tabs\Tab::make('Attendance')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Livewire::make(\App\Filament\Widgets\Reports\AttendanceStatusWidget::class),
                                        Livewire::make(\App\Filament\Widgets\Reports\DailyAttendanceTrendWidget::class),
                                        Livewire::make(\App\Filament\Widgets\Reports\ApprovalStatusFunnelWidget::class)
                                            ->columnSpan(2),
                                    ]),
                            ]),

                    ])->columnSpanFull(),
            ]);
    }
}
