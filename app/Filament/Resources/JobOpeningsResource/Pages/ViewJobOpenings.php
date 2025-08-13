<?php

namespace App\Filament\Resources\JobOpeningsResource\Pages;

use App\Filament\Resources\JobOpeningsResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewJobOpenings extends ViewRecord
{
    protected static string $resource = JobOpeningsResource::class;
    
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }
    
    public function getRelationManagers(): array
    {
        return $this->getResource()::getRelations();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('debug_relations')
                ->label('Debug Relations')
                ->color('info')
                ->action(function () {
                    $relations = static::getResource()::getRelations();
                    $attachmentCount = $this->record->attachments()->count();
                    
                    Notification::make()
                        ->title('Debug Info')
                        ->body('Relations: ' . count($relations) . ' found<br>Attachment count: ' . $attachmentCount)
                        ->info()
                        ->send();
                }),
            Action::make('publish')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-up')
                ->tooltip('Publish this opening job to the career page')
                ->label('Publish')
                ->hidden(fn (Model $record) => $record->published_career_site === 1 ?? false)
                ->action(function (Model $record) {
                    $record->published_career_site = 1;
                    $record->save();
                    Notification::make()
                        ->body('Job Opening has been published.')
                        ->success()
                        ->send();
                }),
            Action::make('unpublished')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->tooltip('Unpublished this opening job in the career page')
                ->label('Unpublished')
                ->requiresConfirmation()
                ->hidden(fn (Model $record) => $record->published_career_site === 0 ?? false)
                ->action(function (Model $record) {
                    $record->published_career_site = 0;
                    $record->save();
                    Notification::make()
                        ->body('Job Opening has been unpublished.')
                        ->success()
                        ->send();
                }),
            EditAction::make()
                ->icon('heroicon-o-pencil-square'),
            DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }
}
