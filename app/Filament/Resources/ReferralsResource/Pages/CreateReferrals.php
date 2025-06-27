<?php

namespace App\Filament\Resources\ReferralsResource\Pages;

use App\Filament\Resources\ReferralsResource;
use App\Models\Attachments;
use App\Models\Candidates;
use App\Models\JobCandidates;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateReferrals extends CreateRecord
{
    protected static string $resource = ReferralsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('View Resume')
                ->color('success')
                ->hidden(fn () => !$this->data['resume'] ?? false)
                ->modalContent(function () {
                    $file = $this->data['resume'];
                    $url = $file instanceof TemporaryUploadedFile 
                        ? $file->temporaryUrl() 
                        : Storage::url($file);
                    
                    return view('referral-form.referral-component.resume-viewer', ['url' => $url]);
                })
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalWidth('7xl')
                ->modalHeading('Resume Viewer')
                ->extraModalWindowAttributes([
                    'class' => 'max-h-screen',
                ]),
            Action::make('Download Resume')
                ->color('primary')
                ->hidden(fn () => !$this->data['resume'] ?? false)
                ->action(function () {
                    $file = $this->data['resume'];
                    if ($file instanceof TemporaryUploadedFile) {
                        return response()->download($file->getRealPath(), $file->getClientOriginalName());
                    }
                    return response()->download(storage_path('app/public/' . $file));
                }),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ReferredBy'] = auth()->id();
        $data['AssignedRecruiter'] = auth()->id();
        
        try {
            $candidate = Candidates::findOrFail($data['Candidate']);
            
            $jobCandidates = JobCandidates::create([
                'JobId' => $data['ReferringJob'],
                'candidate' => $data['Candidate'],
                'mobile' => $candidate->Mobile,
                'Email' => $candidate->email,
                'ExperienceInYears' => $candidate->ExperienceInYears,
                'CurrentJobTitle' => $candidate->CurrentJobTitle,
                'CurrentEmployer' => $candidate->CurrentEmployer,
                'City' => $candidate->City,
                'State' => $candidate->State,
                'CandidateStatus' => 'New',
                'CandidateSource' => 'Referral',
                'CandidateOwner' => auth()->id(),
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ]);

            // Handle resume upload - store in referrals table
            if (isset($data['resume']) && $data['resume'] instanceof TemporaryUploadedFile) {
                $file = $data['resume'];
                
                // Generate filename in exact format: [OriginalName]_[12 character random string].pdf
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $randomString = Str::lower(Str::random(12)); // 12 character random string
                $uniqueName = "{$originalName}_{$randomString}.{$extension}";
                
                $path = $file->storeAs('referrals/resumes', $uniqueName, 'public');
                $data['resume'] = $path;
                
                Attachments::create([
                    'attachment' => $path,
                    'attachmentName' => $file->getClientOriginalName(),
                    'category' => 'Resume',
                    'moduleName' => 'JobCandidates',
                    'attachmentOwner' => $jobCandidates->id,
                    'CreatedBy' => auth()->id(),
                ]);
            }

            $data['JobCandidate'] = $jobCandidates->id;

        } catch (ModelNotFoundException $e) {
            throw new \Exception("Candidate not found");
        } catch (\Exception $e) {
            throw new \Exception("Failed to create referral: " . $e->getMessage());
        }

        return $data;
    }
}