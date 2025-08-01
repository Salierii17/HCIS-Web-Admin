<?php

namespace App\Filament\Resources\MaterialResource\Pages;

use App\Filament\Resources\MaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\Facades\Storage;

class ViewMaterial extends ViewRecord
{
    protected static string $resource = MaterialResource::class;

    //  protected function getViewData(): array
    // {
    //     return [
    //         'record' => $this->record,
    //     ];
    // }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
            // Display the material name, spanning the full width
            TextEntry::make('nama_material')
                ->label('Nama Material')
                ->view('livewire.centered-title')
                ->columnSpanFull(), // Add this line

            // Custom view component to display the PDF, also spanning the full width
            ViewEntry::make('file_path')
                ->label('File Preview')
                ->view('livewire.pdf-viewer')
                ->columnSpanFull(), // And add this line
        ]);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
