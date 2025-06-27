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
                ->hidden(fn () => !$this->record->resume)
                ->modalContent(fn () => view('referral-form.referral-component.resume-viewer', [
                    'url' => Storage::url($this->record->resume)
                ]))
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalWidth('7xl')
                ->modalHeading('Resume Viewer')
                ->extraModalWindowAttributes([
                    'class' => 'max-h-screen',
                ]),
            Action::make('Download Resume')
                ->color('primary')
                ->hidden(fn () => !$this->record->resume)
                ->action(function () {
                    return response()->download(storage_path('app/public/' . $this->record->resume));
                }),
        ];
    }
}