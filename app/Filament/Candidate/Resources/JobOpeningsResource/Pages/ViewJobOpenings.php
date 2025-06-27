<?php

namespace App\Filament\Candidate\Resources\JobOpeningsResource\Pages;

use App\Filament\Candidate\Pages\MyResumeProfile;
use App\Filament\Candidate\Resources\JobOpeningsResource;
use App\Filament\Enums\JobCandidateStatus;
use App\Models\Candidates;
use App\Models\CandidateUser;
use App\Models\JobCandidates;
use App\Models\SavedJob;
use Filament\Actions\Action;
use Filament\Notifications;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;
use App\Models\Attachments;

class ViewJobOpenings extends ViewRecord
{
    protected static string $resource = JobOpeningsResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('save_job')
                ->icon(function () {
                    return $this->isAlreadySaveJob() === true ? 'heroicon-s-heart' : 'heroicon-o-heart';
                })
                ->color(Color::Red)
                ->label(function () {
                    return $this->isAlreadySaveJob() === true ? 'Job Saved' : 'Save Job';
                })
                ->action(fn () => $this->saveJob()),
            Action::make('apply')
                ->label('Apply Job')
                ->icon('heroicon-o-briefcase')
                ->color(Color::Green)
                ->requiresConfirmation()
                ->modalDescription(function () {
                    return new HtmlString('Are you sure you want to send your Resume without updating your resume?'.
                        '<br><br><i style="color: indianred">Note: You cannot update your submitted resume to the Job you applied.</i>');

                })
                ->modalCancelAction(function () {
                    return Action::make('Update Resume')
                        ->color(Color::Teal)
                        ->url(MyResumeProfile::getUrl());
                })
                ->modalCancelActionLabel('Update Resume')
                ->action(fn () => $this->applyJob()),

        ];
    }

    private function saveJob(): void
    {
        $id = $this->record->id;
        // check if the job is already saved before or not
        if (! $this->isAlreadySaveJob()) {
            // Save the Job
            SavedJob::create([
                'job' => $id,
                'record_owner' => auth()->id(),
            ])->save();
            Notifications\Notification::make()
                ->color(Color::Green)
                ->icon('heroicon-o-check-circle')
                ->body('Job has been added to your job list.')
                ->send();
            // force refresh the page.
            $this->redirect(url(JobOpeningsResource::getUrl('view', [$id])));
        } else {
            // remove the save job
            SavedJob::whereJob($id)->whereRecordOwner(auth()->user()->id)->delete();
            Notifications\Notification::make()
                ->color(Color::Green)
                ->icon('heroicon-o-check-circle')
                ->body('Job has been removed to your job list.')
                ->send();
            // force refresh the page.
            $this->redirect(url(JobOpeningsResource::getUrl('view', [$id])));
        }

    }

    private function applyJob(): void
    {
        // Make sure that the applicant didn't applied to the same job using the email address
        $myAppliedJobs = CandidateUser::find(auth()->id())->myAppliedJobs()->whereId($this->record->id)->get()->toArray();
        
        if (count($myAppliedJobs) > 0) {
            Notifications\Notification::make()
                ->color(Color::Orange)
                ->icon('heroicon-o-exclamation-circle')
                ->body('You\'ve already applied this job.')
                ->send();
            return;
        }

        $candidateProfile = $this->getMyCandidateProfile()->first();
        
        if (!$candidateProfile) {
            Notifications\Notification::make()
                ->color(Color::Orange)
                ->icon('heroicon-o-exclamation-circle')
                ->title('Applying Job Cancelled.')
                ->body('Please update your resume profile before applying a job.')
                ->actions([
                    Notifications\Actions\Action::make('update_resume')
                        ->label('Update Resume')
                        ->icon('heroicon-o-document-text')
                        ->color(Color::Green)
                        ->url(MyResumeProfile::getUrl())
                        ->button(),
                    Notifications\Actions\Action::make('maybe_later')
                        ->label('Maybe Later')
                        ->button(),
                ])
                ->persistent()
                ->send();
            return;
        }

        try {
            // create a candidate job record
            $job = JobCandidates::create([
                'JobId' => $this->record->id,
                'CandidateSource' => 'Portal',
                'candidate' => $candidateProfile->id,
                'mobile' => $candidateProfile->Mobile ?? null,
                'Email' => $candidateProfile->Email ?? $candidateProfile->email ?? null,
                'ExperienceInYears' => $candidateProfile->ExperienceInYears ?? null,
                'ExpectedSalary' => $candidateProfile->ExpectedSalary ?? null,
                'HighestQualificationHeld' => $candidateProfile->HighestQualificationHeld ?? null,
                'CurrentEmployer' => $candidateProfile->CurrentEmployer ?? null,
                'CurrentJobTitle' => $candidateProfile->CurrentJobTitle ?? null,
                'CurrentSalary' => $candidateProfile->CurrentSalary ?? null,
                'CandidateStatus' => JobCandidateStatus::New,
                'SkillSet' => $candidateProfile->SkillSet ?? null,
                'Street' => $candidateProfile->Street ?? null,
                'City' => $candidateProfile->City ?? null,
                'Country' => $candidateProfile->Country ?? null,
                'ZipCode' => $candidateProfile->ZipCode ?? null,
                'State' => $candidateProfile->State ?? null,
            ]);

            // Get the latest resume from candidate
            $latestResume = $candidateProfile->resume()->latest()->first();
            
            if ($latestResume) {
                // Create attachment for this job candidate
                Attachments::create([
                    'attachment' => $latestResume->attachment,
                    'attachmentName' => $latestResume->attachmentName,
                    'category' => 'Resume',
                    'attachmentOwner' => $job->id,
                    'moduleName' => 'JobCandidates'
                ]);
            }

            Notifications\Notification::make()
                ->color(Color::Green)
                ->icon('heroicon-o-check-circle')
                ->title('Job Applied')
                ->body('Your resume and data has been sent to the hiring party.')
                ->send();

        } catch (\Exception $e) {
            Notifications\Notification::make()
                ->color(Color::Red)
                ->icon('heroicon-o-exclamation-circle')
                ->title('Application Failed')
                ->body('There was an error submitting your application. Please try again.')
                ->send();
            
            report($e); // Log the error for debugging
        }
    }

    protected function isAlreadySaveJob(): bool
    {
        $existing = SavedJob::whereJob($this->record->id)->whereRecordOwner(auth()->user()->id)->count();

        return $existing > 0;

    }

    protected function getMyCandidateProfile(): Collection
    {
        // Key matching using the login email address
        return Candidates::where('Email', '=', auth()->user()->email)->get();
    }
}
