<?php

namespace App\Filament\Resources\AssignTrainingResource\Pages;

use App\Filament\Resources\AssignTrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssignTrainings extends ListRecords
{
    protected static string $resource = AssignTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
