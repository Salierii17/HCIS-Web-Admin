<?php

namespace App\Filament\Resources\ReferralsResource\Pages;

use App\Filament\Resources\ReferralsResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewReferrals extends ViewRecord
{
    protected static string $resource = ReferralsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Action::make('View Resume')
                ->color('success')
                ->hidden(fn () => ! $this->record->resume)
                ->url(fn () => Storage::url($this->record->resume))
                ->openUrlInNewTab()
                ->icon('heroicon-o-eye'),
            Action::make('Download Resume')
                ->color('primary')
                ->icon('heroicon-o-arrow-down-tray')
                ->hidden(fn () => ! $this->record->resume)
                ->action(function () {
                    $resumePath = $this->record->resume;
                    if ($resumePath && Storage::disk('public')->exists($resumePath)) {
                        return response()->download(storage_path('app/public/'.$resumePath));
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Resume not found')
                        ->danger()
                        ->send();
                }),
        ];
    }
}
