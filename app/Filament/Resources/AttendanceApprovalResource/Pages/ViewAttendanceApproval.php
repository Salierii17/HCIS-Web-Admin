<?php

namespace App\Filament\Resources\AttendanceApprovalResource\Pages;

use App\Filament\Resources\AttendanceApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendanceApproval extends ViewRecord
{
    protected static string $resource = AttendanceApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
