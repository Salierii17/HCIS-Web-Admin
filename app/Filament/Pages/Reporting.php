<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Reporting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static string $view = 'filament.pages.reporting';

    protected static ?int $navigationSort = 1;

}
