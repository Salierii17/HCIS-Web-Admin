<?php

namespace App\Filament\Resources\JobOpeningsResource\RelationManagers;

use App\Filament\Enums\AttachmentCategory;
use App\Models\Attachments;
use App\Models\Candidates;
use App\Models\JobCandidates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $recordTitleAttribute = 'attachmentName';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('attachment')
                    ->preserveFilenames()
                    ->storeFileNamesIn('attachmentName')
                    ->directory('JobOpening-attachments')
                    ->visibility('public')
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                    ])
                    ->disabled(fn ($record) => $record?->category === 'Resume') // Disable for resumes
                    ->required(),
                Forms\Components\TextInput::make('attachmentName')
                    ->disabledOn('edit')
                    ->hidden()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->options(AttachmentCategory::class)
                    ->required(),
                Forms\Components\TextInput::make('attachmentOwner')
                    ->readOnly()
                    ->default(fn ($livewire) => $livewire->ownerRecord->id)
                    ->required()
                    ->hidden()
                    ->maxLength(255),
                Forms\Components\TextInput::make('moduleName')
                    ->readOnly()
                    ->default('JobOpening')
                    ->hidden()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                // Debug: Let's see what we're working with
                $jobOpeningId = $this->ownerRecord->id;

                // Get all job candidates for this job opening
                $jobCandidates = JobCandidates::where('JobId', $jobOpeningId)
                    ->with(['candidateProfile.attachments' => function ($query) {
                        $query->where('moduleName', 'Candidates');
                    }])
                    ->get();

                // Get Candidates attachments from these candidates
                $candidateAttachments = $jobCandidates->flatMap(function ($jobCandidate) {
                    return $jobCandidate->candidateProfile?->attachments ?? collect();
                });

                // Get direct JobCandidates attachments (resumes submitted for this specific job)
                $jobCandidateAttachments = Attachments::where('moduleName', 'JobCandidates')
                    ->whereIn('attachmentOwner', $jobCandidates->pluck('id'))
                    ->get();

                // Get direct job opening attachments
                $jobOpeningAttachments = $this->ownerRecord->attachments()
                    ->where('moduleName', 'JobOpening')
                    ->get();

                // Combine all attachment IDs
                $allAttachmentIds = collect()
                    ->merge($candidateAttachments->pluck('id'))
                    ->merge($jobCandidateAttachments->pluck('id'))
                    ->merge($jobOpeningAttachments->pluck('id'))
                    ->unique()
                    ->filter()
                    ->toArray();

                // Debug info (you can remove this later)
                if (app()->environment('local')) {
                    \Log::info('JobOpening Attachments Debug', [
                        'job_opening_id' => $jobOpeningId,
                        'job_candidates_count' => $jobCandidates->count(),
                        'candidate_attachments_count' => $candidateAttachments->count(),
                        'job_candidate_attachments_count' => $jobCandidateAttachments->count(),
                        'job_opening_attachments_count' => $jobOpeningAttachments->count(),
                        'total_attachment_ids' => count($allAttachmentIds),
                        'attachment_ids' => $allAttachmentIds,
                    ]);
                }

                if (empty($allAttachmentIds)) {
                    return Attachments::whereRaw('1 = 0'); // Return empty query
                }

                return Attachments::whereIn('id', $allAttachmentIds);
            })
            ->recordUrl(function ($record) {
                if ($record->moduleName === 'Candidates') {
                    $jobCandidate = JobCandidates::where('candidate', $record->attachmentOwner)
                        ->where('JobId', $this->ownerRecord->id)
                        ->first();

                    if ($jobCandidate) {
                        return \App\Filament\Resources\JobCandidatesResource::getUrl('view', [$jobCandidate->id]);
                    }
                } elseif ($record->moduleName === 'JobCandidates') {
                    return \App\Filament\Resources\JobCandidatesResource::getUrl('view', [$record->attachmentOwner]);
                }

                return null;
            })
            ->columns([
                Tables\Columns\TextColumn::make('candidate_name')
                    ->label('Candidate')
                    ->getStateUsing(function ($record) {
                        if ($record->moduleName === 'Candidates') {
                            $candidate = Candidates::find($record->attachmentOwner);

                            return $candidate ? $candidate->full_name : 'Unknown Candidate';
                        } elseif ($record->moduleName === 'JobCandidates') {
                            $jobCandidate = JobCandidates::find($record->attachmentOwner);
                            if ($jobCandidate && $jobCandidate->candidateProfile) {
                                return $jobCandidate->candidateProfile->full_name;
                            }

                            return 'Job Candidate';
                        }

                        return 'Job Opening Document';
                    })
                    ->url(function ($record) {
                        if ($record->moduleName === 'Candidates' && $record->attachmentOwner) {
                            return \App\Filament\Resources\CandidatesProfileResource::getUrl('view', [
                                'record' => $record->attachmentOwner,
                            ]);
                        } elseif ($record->moduleName === 'JobCandidates' && $record->attachmentOwner) {
                            return \App\Filament\Resources\JobCandidatesResource::getUrl('view', [$record->attachmentOwner]);
                        }

                        return null;
                    })
                    ->openUrlInNewTab(false)
                    ->icon(function ($record) {
                        if (in_array($record->moduleName, ['Candidates', 'JobCandidates'])) {
                            return 'heroicon-m-arrow-top-right-on-square';
                        }

                        return null;
                    })
                    ->iconPosition('after')
                    ->extraAttributes([
                        'class' => 'hover:underline',
                    ]),
                Tables\Columns\TextColumn::make('attachmentName')
                    ->label('File Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('moduleName')
                    ->label('Source')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'JobOpening' => 'primary',
                        'JobCandidates' => 'success',
                        'Candidates' => 'warning',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('moduleName')
                    ->label('Source')
                    ->options([
                        'JobOpening' => 'Job Opening Documents',
                        'JobCandidates' => 'Job Application Files',
                        'Candidates' => 'Candidate Profile Files',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options(AttachmentCategory::class),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(function ($record) {
                        $filePath = str_replace('public/', '', $record->attachment);

                        return asset('storage/'.$filePath);
                    })
                    ->openUrlInNewTab()
                    ->hidden(fn ($record) => ! $record->attachment),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        $path = storage_path('app/public/'.$record->attachment);

                        return response()->download($path, $record->attachmentName);
                    })
                    ->hidden(fn ($record) => ! $record->attachment),

                Tables\Actions\Action::make('status')
                    ->label('View Application')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('warning')
                    ->url(function ($record) {
                        if ($record->moduleName === 'Candidates') {
                            $jobCandidate = JobCandidates::where('candidate', $record->attachmentOwner)
                                ->where('JobId', $this->ownerRecord->id)
                                ->first();
                            if ($jobCandidate) {
                                return \App\Filament\Resources\JobCandidatesResource::getUrl('view', [$jobCandidate->id]);
                            }
                        } elseif ($record->moduleName === 'JobCandidates') {
                            return \App\Filament\Resources\JobCandidatesResource::getUrl('view', [$record->attachmentOwner]);
                        }

                        return null;
                    })
                    ->hidden(fn ($record) => ! in_array($record->moduleName, ['JobCandidates', 'Candidates'])),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->moduleName === 'JobOpening'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->moduleName === 'JobOpening'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->moduleName === 'JobOpening') {
                                    $record->delete();
                                }
                            });
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
