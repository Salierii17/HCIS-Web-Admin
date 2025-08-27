<?php

namespace App\Filament\Resources\AttendanceApprovalResource\Pages;

use App\Filament\Resources\AttendanceApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceApproval extends EditRecord
{
    protected static string $resource = AttendanceApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
