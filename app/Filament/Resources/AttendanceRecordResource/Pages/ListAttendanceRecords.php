<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Resources\AttendanceRecordResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceRecords extends ListRecords
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus-small'),
        ];
    }
}
