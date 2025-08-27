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
            Action::make('debug_attachments')
                ->label('Debug Attachments')
                ->color('info')
                ->action(function () {
                    $jobOpeningId = $this->record->id;

                    $jobCandidates = \App\Models\JobCandidates::where('JobId', $jobOpeningId)->get();
                    $jobCandidateAttachments = \App\Models\Attachments::where('moduleName', 'JobCandidates')
                        ->whereIn('attachmentOwner', $jobCandidates->pluck('id'))->count();
                    $jobOpeningAttachments = $this->record->attachments()->where('moduleName', 'JobOpening')->count();
                    $candidateAttachments = \App\Models\Attachments::where('moduleName', 'Candidates')
                        ->whereIn('attachmentOwner', $jobCandidates->pluck('candidate'))->count();

                    Notification::make()
                        ->title('Attachment Debug')
                        ->body(
                            'Job Opening ID: '.$jobOpeningId.'<br>'.
                            'Job Candidates: '.$jobCandidates->count().'<br>'.
                            'Job Opening Attachments: '.$jobOpeningAttachments.'<br>'.
                            'Job Candidate Attachments: '.$jobCandidateAttachments.'<br>'.
                            'Candidate Profile Attachments: '.$candidateAttachments
                        )
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
