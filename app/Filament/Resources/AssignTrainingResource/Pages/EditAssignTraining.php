<?php

namespace App\Filament\Resources\AssignTrainingResource\Pages;

use App\Filament\Resources\AssignTrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignTraining extends EditRecord
{
    protected static string $resource = AssignTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
