<?php

namespace App\Filament\Resources\TryoutResource\Pages;

use App\Filament\Resources\TryoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTryouts extends ListRecords
{
    protected static string $resource = TryoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
