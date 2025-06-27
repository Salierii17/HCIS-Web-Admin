<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Attachments;
use App\Models\Candidates;
use App\Models\JobCandidates;
use App\Models\Referrals;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MyResumeProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected ?string $subheading = 'This profile will be used once you apply for a job in the portal.';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.candidate.pages.my-resume-profile';
    
    public ?array $data = [];
    public TemporaryUploadedFile|array|null $resumeFile = null;
    public ?string $resumeUrl = null;
    public ?string $resumeFileName = null;
    public ?string $candidateSource = null;

    public function mount(): void
    {
        $user = Filament::auth()->user();
        $candidate = Candidates::where('Email', $user->email)->first();
        
        if ($candidate) {
            $this->candidateSource = $candidate->jobCandidates()->first()?->CandidateSource;
            $this->loadResumeUrl();
        }

        $this->form->fill($candidate?->toArray() ?? []);
    }

    protected function loadResumeUrl(): void
    {
        $user = Filament::auth()->user();
        $candidate = Candidates::where('Email', $user->email)->first();
        
        if (!$candidate) return;

        if ($this->candidateSource === 'Referral') {
            $referral = $candidate->referral;
            if ($referral && $referral->resume) {
                $this->resumeUrl = asset('storage/' . $referral->resume);
                $this->resumeFileName = basename($referral->resume);
            }
        } else {
            $resume = $candidate->resume()->first();
            if ($resume) {
                $this->resumeUrl = asset('storage/' . str_replace('public/', '', $resume->attachment));
                $this->resumeFileName = $resume->attachmentName;
            }
        }
    }

    public function updateRecord(): void
    {
        try {
            $validatedData = $this->form->getState();
            
            Candidates::updateOrCreate(
                ['Email' => Filament::auth()->user()->email],
                $validatedData
            );
            
            Notification::make()
                ->title('Profile information updated')
                ->success()
                ->body('Your profile information has been updated successfully.')
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error updating profile')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public function updateResume(): void
    {
        try {
            $user = Filament::auth()->user();
            $candidate = Candidates::where('Email', $user->email)->firstOrFail();
            
            if (!$this->resumeFile) {
                throw new \Exception('Please select a resume file to upload');
            }

            // Generate unique filename
            $originalName = pathinfo($this->resumeFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $this->resumeFile->getClientOriginalExtension();
            $uniqueName = $originalName . '_' . uniqid() . '.' . $extension;

            if ($this->candidateSource === 'Referral') {
                $this->updateReferralResume($candidate, $uniqueName);
            } else {
                $this->updateAttachmentResume($candidate, $uniqueName);
            }

            // Clear the file input after successful upload
            $this->resumeFile = null;
            $this->form->fill(['resumeFile' => null]);
            
            $this->loadResumeUrl();
            
            Notification::make()
                ->title('Resume Updated Successfully')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Updating Resume')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function updateReferralResume(Candidates $candidate, string $uniqueName): void
    {
        $path = $this->resumeFile->storeAs('referrals/resumes', $uniqueName, 'public');
        
        // Update existing referral record
        $candidate->referral()->update([
            'resume' => $path
        ]);
    }

    protected function updateAttachmentResume(Candidates $candidate, string $uniqueName): void
    {
        $path = $this->resumeFile->storeAs('JobCandidate-attachments', $uniqueName, 'public');
        $originalName = $this->resumeFile->getClientOriginalName();

        // Update or create attachment for Candidates module
        $candidate->resume()->updateOrCreate(
            ['moduleName' => 'Candidates'],
            [
                'attachment' => $path,
                'attachmentName' => $originalName,
                'category' => 'Resume'
            ]
        );

        // Update all JobCandidates attachments for this candidate
        foreach ($candidate->jobCandidates as $jobCandidate) {
            $jobCandidate->resume()->updateOrCreate(
                [
                    'moduleName' => 'JobCandidates',
                    'category' => 'Resume'
                ],
                [
                    'attachment' => $path,
                    'attachmentName' => $originalName
                ]
            );
        }
    }

    // public function updateResume(): void
    // {
    //     try {
    //         $user = Filament::auth()->user();
    //         $candidate = Candidates::where('Email', $user->email)->firstOrFail();
            
    //         if (!$this->resumeFile) {
    //             throw new \Exception('Please select a resume file to upload');
    //         }

    //         // Ensure we have a valid uploaded file
    //         if (!$this->resumeFile instanceof TemporaryUploadedFile) {
    //             throw new \Exception('Invalid file upload');
    //         }

    //         // Generate unique filename
    //         $originalName = pathinfo($this->resumeFile->getClientOriginalName(), PATHINFO_FILENAME);
    //         $extension = $this->resumeFile->getClientOriginalExtension();
    //         $uniqueName = $originalName . '_' . uniqid() . '.' . $extension;

    //         if ($this->candidateSource === 'Referral') {
    //             $this->updateReferralResume($candidate, $uniqueName);
    //         } else {
    //             $this->updateAttachmentResume($candidate, $uniqueName);
    //         }

    //         // Clear the file input after successful upload
    //         $this->resumeFile = null;
    //         $this->form->fill(['resumeFile' => null]);
            
    //         $this->loadResumeUrl();
            
    //         Notification::make()
    //             ->title('Resume Updated Successfully')
    //             ->success()
    //             ->send();
                
    //     } catch (\Exception $e) {
    //         Notification::make()
    //             ->title('Error Updating Resume')
    //             ->danger()
    //             ->body($e->getMessage())
    //             ->send();
    //     }
    // }

    // public function updateResume(): void
    // {
    //     try {
    //         $user = Filament::auth()->user();
    //         $candidate = Candidates::where('Email', $user->email)->firstOrFail();
            
    //         if (!$this->resumeFile) {
    //             throw new \Exception('Please select a resume file to upload');
    //         }

    //         // Generate unique filename
    //         $originalName = pathinfo($this->resumeFile->getClientOriginalName(), PATHINFO_FILENAME);
    //         $extension = $this->resumeFile->getClientOriginalExtension();
    //         $uniqueName = $originalName . '_' . uniqid() . '.' . $extension;

    //         if ($this->candidateSource === 'Referral') {
    //             $this->updateReferralResume($candidate, $uniqueName);
    //         } else {
    //             $this->updateAttachmentResume($candidate, $uniqueName);
    //         }

    //         $this->loadResumeUrl();
            
    //         Notification::make()
    //             ->title('Resume Updated Successfully')
    //             ->success()
    //             ->send();
                
    //     } catch (\Exception $e) {
    //         Notification::make()
    //             ->title('Error Updating Resume')
    //             ->danger()
    //             ->body($e->getMessage())
    //             ->send();
    //     }
    // }

    // protected function updateReferralResume(Candidates $candidate, string $uniqueName): void
    // {
    //     $path = $this->resumeFile->storeAs('referrals/resumes', $uniqueName, 'public');
        
    //     // Update existing referral record
    //     $candidate->referral()->update([
    //         'resume' => $path
    //     ]);
    // }

    // protected function updateAttachmentResume(Candidates $candidate, string $uniqueName): void
    // {
    //     $path = $this->resumeFile->storeAs('JobCandidate-attachments', $uniqueName, 'public');
    //     $originalName = $this->resumeFile->getClientOriginalName();

    //     // Update both attachment records
    //     $candidate->resume()->updateOrCreate(
    //         ['moduleName' => 'Candidates'],
    //         [
    //             'attachment' => $path,
    //             'attachmentName' => $originalName,
    //             'category' => 'Resume'
    //         ]
    //     );

    //     if ($jobCandidate = $candidate->jobCandidates()->first()) {
    //         Attachments::updateOrCreate(
    //             [
    //                 'attachmentOwner' => $jobCandidate->id,
    //                 'moduleName' => 'JobCandidates',
    //                 'category' => 'Resume'
    //             ],
    //             [
    //                 'attachment' => $path,
    //                 'attachmentName' => $originalName
    //             ]
    //         );
    //     }
    // }

    public function downloadResume()
    {
        $user = Filament::auth()->user();
        $candidate = Candidates::where('Email', $user->email)->first();
        
        if (!$candidate) {
            Notification::make()
                ->title('No candidate profile found')
                ->warning()
                ->send();
            return;
        }

        if ($this->candidateSource === 'Referral') {
            $referral = $candidate->referral;
            if ($referral && $referral->resume) {
                return response()->download(
                    storage_path('app/public/' . $referral->resume),
                    $this->resumeFileName
                );
            }
        } else {
            $resume = $candidate->resume()->first();
            if ($resume) {
                return response()->download(
                    storage_path('app/public/' . $resume->attachment),
                    $resume->attachmentName
                );
            }
        }

        Notification::make()
            ->title('No resume found')
            ->warning()
            ->send();
    }

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';
    // protected ?string $subheading = 'This profile will be used once you apply for a job in the portal.';
    // protected static ?int $navigationSort = 4;

    // protected static string $view = 'filament.candidate.pages.my-resume-profile';
    
    // public ?array $data = [];
    // public TemporaryUploadedFile|array|null $resumeFile = null;
    // public ?string $resumeUrl = null;
    // public ?string $resumeFileName = null;

    // public function mount(): void
    // {
    //     $user = Filament::auth()->user();
    //     $profile = $this->getResumeProfile();
        
    //     // Initialize with default values
    //     $this->data = [
    //         'Email' => $user->email ?? '',
    //         'FirstName' => '',
    //         'LastName' => '',
    //         'Mobile' => '',
    //         'ExperienceInYears' => '',
    //         'ExpectedSalary' => '',
    //         'HighestQualificationHeld' => '',
    //         'CurrentEmployer' => '',
    //         'CurrentJobTitle' => '',
    //         'CurrentSalary' => '',
    //         'Street' => '',
    //         'City' => '',
    //         'Country' => '',
    //         'ZipCode' => '',
    //         'State' => '',
    //         'ExperienceDetails' => [],
    //         'SkillSet' => [],
    //         'School' => [],
    //     ];

    //     // Merge with existing profile data if available
    //     if ($profile->isNotEmpty()) {
    //         $profileData = $profile->first()->toArray();
    //         $this->data = array_merge($this->data, $profileData);
    //     }

    //     $this->form->fill($this->data);
    //     $this->loadResumeUrl();
    // }

    // protected function loadResumeUrl(): void
    // {
    //     $user = Filament::auth()->user();
    //     $candidate = Candidates::where('Email', $user->email)->first();
        
    //     if (!$candidate) return;

    //     // Check if candidate has a referral
    //     if ($candidate->referral) {
    //         $this->resumeUrl = $candidate->referral->resume 
    //             ? asset('storage/' . $candidate->referral->resume)
    //             : null;
    //         $this->resumeFileName = $this->resumeUrl ? basename($candidate->referral->resume) : null;
    //     } else {
    //         // Check attachments table
    //         $resume = $candidate->resume()->first();
    //         if ($resume) {
    //             $this->resumeUrl = asset('storage/' . str_replace('public/', '', $resume->attachment));
    //             $this->resumeFileName = $resume->attachmentName;
    //         }
    //     }
    // }

    // protected function getResumeProfile(): Collection
    // {
    //     $user = Filament::auth()->user();
        
    //     if (!$user || !$user->email) {
    //         return new Collection();
    //     }

    //     return Candidates::where('Email', $user->email)->get();
    // }

    // public function updateRecord(): void
    // {
    //     try {
    //         $validatedData = $this->form->getState();
            
    //         $candidate = Candidates::updateOrCreate(
    //             ['Email' => Filament::auth()->user()->email],
    //             $validatedData
    //         );
            
    //         // Handle resume upload if present
    //         if ($this->resumeFile) {
    //             $this->handleResumeUpload($candidate);
    //         }
            
    //         Notification::make()
    //             ->title('Profile information updated')
    //             ->success()
    //             ->body('Your profile information has been updated successfully.')
    //             ->send();
                
    //     } catch (\Exception $e) {
    //         Notification::make()
    //             ->title('Error updating profile')
    //             ->danger()
    //             ->body($e->getMessage())
    //             ->send();
    //     }
    // }

    // protected function handleResumeUpload(Candidates $candidate): void
    // {
    //     // Delete existing resume if any
    //     $candidate->resume()->delete();

    //     // Store new resume
    //     $filename = $this->resumeFile->getClientOriginalName();
        
    //     $attachment = new Attachments([
    //         'attachment' => $this->resumeFile->store('resumes', 'public'),
    //         'attachmentName' => $filename,
    //         'category' => 'Resume',
    //         'attachmentOwner' => $candidate->id,
    //         'moduleName' => 'Candidates',
    //     ]);
        
    //     $candidate->attachments()->save($attachment);
    // }

    // public function downloadResume()
    // {
    //     $user = Filament::auth()->user();
    //     $candidate = Candidates::where('Email', $user->email)->first();
        
    //     if (!$candidate) {
    //         Notification::make()
    //             ->title('No candidate profile found')
    //             ->warning()
    //             ->send();
    //         return;
    //     }

    //     // Handle referral candidates
    //     if ($candidate->referral && $candidate->referral->resume) {
    //         return response()->download(
    //             storage_path('app/public/' . $candidate->referral->resume),
    //             $this->resumeFileName
    //         );
    //     }

    //     // Handle regular candidates
    //     $resume = $candidate->resume()->first();
    //     if ($resume) {
    //         return response()->download(
    //             storage_path('app/public/' . $resume->attachment),
    //             $resume->attachmentName
    //         );
    //     }

    //     Notification::make()
    //         ->title('No resume found')
    //         ->warning()
    //         ->send();
    // }

    // public function updateResume(): void
    // {
    //     try {
    //         $user = Filament::auth()->user();
    //         $candidate = Candidates::where('Email', $user->email)->firstOrFail();
            
    //         if (!$this->resumeFile) {
    //             throw new \Exception('Please select a resume file to upload');
    //         }

    //         // Generate unique filename
    //         $originalName = pathinfo($this->resumeFile->getClientOriginalName(), PATHINFO_FILENAME);
    //         $extension = $this->resumeFile->getClientOriginalExtension();
    //         $uniqueName = $originalName . '_' . uniqid() . '.' . $extension;

    //         if ($candidate->referral) {
    //             $this->updateReferralResume($candidate, $uniqueName);
    //         } else {
    //             $this->updateAttachmentResume($candidate, $uniqueName);
    //         }

    //         $this->loadResumeUrl();
            
    //         Notification::make()
    //             ->title('Resume Updated Successfully')
    //             ->success()
    //             ->send();
                
    //     } catch (\Exception $e) {
    //         Notification::make()
    //             ->title('Error Updating Resume')
    //             ->danger()
    //             ->body($e->getMessage())
    //             ->send();
    //     }
    // }

    // // public function updateResume(): void
    // // {
    // //     try {
    // //         $user = Filament::auth()->user();
    // //         $candidate = Candidates::where('Email', $user->email)->first();
            
    // //         if (!$candidate) {
    // //             throw new \Exception('Candidate profile not found');
    // //         }
            
    // //         if (!$this->resumeFile) {
    // //             throw new \Exception('No resume file selected');
    // //         }

    // //         // Generate unique filename
    // //         $originalName = pathinfo($this->resumeFile->getClientOriginalName(), PATHINFO_FILENAME);
    // //         $extension = $this->resumeFile->getClientOriginalExtension();
    // //         $randomString = substr(md5(uniqid()), 0, 12); // 12 character random string
    // //         $uniqueName = "{$originalName}_{$randomString}.{$extension}";
            
    // //         // Handle referral candidates
    // //         if ($candidate->referral) {
    // //             $this->updateReferralResume($candidate, $uniqueName);
    // //         } 
    // //         // Handle regular candidates
    // //         else {
    // //             $this->updateAttachmentResume($candidate, $uniqueName);
    // //         }
            
    // //         $this->loadResumeUrl();
            
    // //         Notification::make()
    // //             ->title('Resume updated')
    // //             ->success()
    // //             ->body('Your resume has been updated successfully.')
    // //             ->send();
                
    // //     } catch (\Exception $e) {
    // //         Notification::make()
    // //             ->title('Error updating resume')
    // //             ->danger()
    // //             ->body($e->getMessage())
    // //             ->send();
    // //     }
    // // }

    // protected function updateReferralResume(Candidates $candidate, string $uniqueName): void
    // {
    //     $path = $this->resumeFile->storeAs('referrals/resumes', $uniqueName, 'public');
        
    //     // Update existing referral record
    //     $candidate->referral()->update([
    //         'resume' => $path
    //     ]);
    // }

    // protected function updateAttachmentResume(Candidates $candidate, string $uniqueName): void
    // {
    //     $path = $this->resumeFile->storeAs('JobCandidate-attachments', $uniqueName, 'public');
    //     $originalName = $this->resumeFile->getClientOriginalName();

    //     // Update or create attachment for Candidates module
    //     $candidate->resume()->updateOrCreate(
    //         ['moduleName' => 'Candidates'],
    //         [
    //             'attachment' => $path,
    //             'attachmentName' => $originalName,
    //             'category' => 'Resume'
    //         ]
    //     );

    //     // Update or create attachment for JobCandidates module
    //     if ($jobCandidate = $candidate->jobCandidates()->first()) {
    //         Attachments::updateOrCreate(
    //             [
    //                 'attachmentOwner' => $jobCandidate->id,
    //                 'moduleName' => 'JobCandidates',
    //                 'category' => 'Resume'
    //             ],
    //             [
    //                 'attachment' => $path,
    //                 'attachmentName' => $originalName
    //             ]
    //         );
    //     }
    // }

    // // protected function updateReferralResume(Candidates $candidate, string $uniqueName): void
    // // {
    // //     $referral = $candidate->referral;
    // //     if (!$referral) {
    // //         throw new \Exception('Referral record not found');
    // //     }

    // //     // Store new file and update referral record (without deleting old file)
    // //     $path = $this->resumeFile->storeAs('referrals/resumes', $uniqueName, 'public');
    // //     $referral->update(['resume' => $path]);
    // // }

    // // protected function updateAttachmentResume(Candidates $candidate, string $uniqueName): void
    // // {
    // //     $jobCandidate = $candidate->jobCandidates()->first();
    // //     if (!$jobCandidate) {
    // //         throw new \Exception('Job candidate record not found');
    // //     }

    // //     // Store new file
    // //     $path = $this->resumeFile->storeAs('JobCandidate-attachments', $uniqueName, 'public');
    // //     $originalName = $this->resumeFile->getClientOriginalName();

    // //     // Update both attachment records (without deleting old files)
    // //     Attachments::where('attachmentOwner', $candidate->id)
    // //         ->where('moduleName', 'Candidates')
    // //         ->where('category', 'Resume')
    // //         ->update([
    // //             'attachment' => $path,
    // //             'attachmentName' => $originalName,
    // //         ]);

    // //     Attachments::where('attachmentOwner', $jobCandidate->id)
    // //         ->where('moduleName', 'JobCandidates')
    // //         ->where('category', 'Resume')
    // //         ->update([
    // //             'attachment' => $path,
    // //             'attachmentName' => $originalName,
    // //         ]);
    // // }

    // // protected function updateReferralResume(Candidates $candidate, string $uniqueName): void
    // // {
    // //     $referral = $candidate->referral;
        
    // //     // Delete old file if exists
    // //     if ($referral->resume && Storage::exists('public/' . $referral->resume)) {
    // //         Storage::delete('public/' . $referral->resume);
    // //     }
        
    // //     // Store new file
    // //     $path = $this->resumeFile->storeAs('referrals/resumes', $uniqueName, 'public');
    // //     $referral->update(['resume' => $path]);
    // // }

    // // protected function updateAttachmentResume(Candidates $candidate, string $uniqueName): void
    // // {
    // //     $jobCandidate = $candidate->jobCandidates()->first();
        
    // //     if (!$jobCandidate) {
    // //         throw new \Exception('Job candidate record not found');
    // //     }

    // //     // Get existing resumes to delete files
    // //     $existingResumes = Attachments::where(function($query) use ($candidate, $jobCandidate) {
    // //         $query->where('attachmentOwner', $candidate->id)
    // //             ->where('moduleName', 'Candidates')
    // //             ->where('category', 'Resume');
    // //     })->orWhere(function($query) use ($jobCandidate) {
    // //         $query->where('attachmentOwner', $jobCandidate->id)
    // //             ->where('moduleName', 'JobCandidates')
    // //             ->where('category', 'Resume');
    // //     })->get();

    // //     // Delete existing files
    // //     foreach ($existingResumes as $resume) {
    // //         if (Storage::exists('public/' . $resume->attachment)) {
    // //             Storage::delete('public/' . $resume->attachment);
    // //         }
    // //     }

    // //     // Delete existing records
    // //     Attachments::where('attachmentOwner', $candidate->id)
    // //         ->where('moduleName', 'Candidates')
    // //         ->where('category', 'Resume')
    // //         ->delete();
            
    // //     Attachments::where('attachmentOwner', $jobCandidate->id)
    // //         ->where('moduleName', 'JobCandidates')
    // //         ->where('category', 'Resume')
    // //         ->delete();
            
    // //     // Store new file
    // //     $path = $this->resumeFile->storeAs('JobCandidate-attachments', $uniqueName, 'public');
    // //     $originalName = $this->resumeFile->getClientOriginalName();
        
    // //     // Create new records
    // //     Attachments::create([
    // //         'attachment' => $path,
    // //         'attachmentName' => $originalName,
    // //         'category' => 'Resume',
    // //         'attachmentOwner' => $candidate->id,
    // //         'moduleName' => 'Candidates',
    // //     ]);
        
    // //     Attachments::create([
    // //         'attachment' => $path,
    // //         'attachmentName' => $originalName,
    // //         'category' => 'Resume',
    // //         'attachmentOwner' => $jobCandidate->id,
    // //         'moduleName' => 'JobCandidates',
    // //     ]);
    // // }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Label')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('FirstName')
                                    ->required()
                                    ->label('First Name'),
                                Forms\Components\TextInput::make('LastName')
                                    ->required()
                                    ->label('Last Name'),
                                Forms\Components\TextInput::make('Mobile')
                                    ->label('Mobile')
                                    ->required()
                                    ->tel(),
                                Forms\Components\TextInput::make('Email')
                                    ->disabled()
                                    ->required(),
                                Forms\Components\Select::make('ExperienceInYears')
                                    ->label('Experience In Years')
                                    ->options([
                                        '1year' => '1 Year',
                                        '2years' => '2 Years',
                                        '3years' => '3 Years',
                                        '4years' => '4 Years',
                                        '5years+' => '5 Years & Above',
                                    ]),
                                Forms\Components\TextInput::make('ExpectedSalary')
                                    ->label('Expected Salary'),
                                Forms\Components\Select::make('HighestQualificationHeld')
                                    ->options([
                                        'Secondary/High School' => 'Secondary/High School',
                                        'Associates Degree' => 'Associates Degree',
                                        'Bachelors Degree' => 'Bachelors Degree',
                                        'Masters Degree' => 'Masters Degree',
                                        'Doctorate Degree' => 'Doctorate Degree',
                                    ])
                                    ->label('Highest Qualification Held'),
                            ]),
                        Tabs\Tab::make('Experience Information')
                            ->icon('phosphor-stack-overflow-logo')
                            ->schema([
                                Forms\Components\Repeater::make('ExperienceDetails')
                                    ->label('')
                                    ->addActionLabel('Add Experience Details')
                                    ->schema([
                                        Forms\Components\Checkbox::make('current')
                                            ->label('Current?')
                                            ->inline(false),
                                        Forms\Components\TextInput::make('company_name'),
                                        Forms\Components\TextInput::make('duration'),
                                        Forms\Components\TextInput::make('role'),
                                        Forms\Components\Textarea::make('company_address'),
                                    ])
                                    ->deleteAction(
                                        fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                                    )
                                    ->columns(5),
                            ]),
                        Tabs\Tab::make('Skill Set Information')
                            ->icon('gameicon-skills')
                            ->schema([
                                Forms\Components\Repeater::make('SkillSet')
                                    ->label('')
                                    ->addActionLabel('Add Another Skill Set')
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('skill')
                                            ->label('Skill'),
                                        Forms\Components\Select::make('proficiency')
                                            ->options([
                                                'Master' => 'Master',
                                                'Intermediate' => 'Intermediate',
                                                'Beginner' => 'Beginner',
                                            ])
                                            ->label('Proficiency'),
                                        Forms\Components\Select::make('experience')
                                            ->options([
                                                '1year' => '1 Year',
                                                '2years' => '2 Years',
                                                '3years' => '3 Years',
                                                '4years' => '4 Years',
                                                '5years' => '5 Years',
                                                '6years' => '6 Years',
                                                '7years' => '7 Years',
                                                '8years' => '8 Years',
                                                '9years' => '9 Years',
                                                '10years+' => '10 Years & Above',
                                            ])
                                            ->label('Experience'),
                                        Forms\Components\Select::make('last_used')
                                            ->options(function () {
                                                $lastUsedOptions = [];
                                                $counter = 30;
                                                for ($i = $counter; $i >= 0; $i--) {
                                                    $lastUsedOptions[
                                                        Carbon::now()->subYear($i)->year
                                                    ] = Carbon::now()->subYear($i)->year;
                                                }
                                                return $lastUsedOptions;
                                            })
                                            ->label('Last Used'),
                                    ]),
                            ]),
                        Tabs\Tab::make('Current Job Information')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Forms\Components\TextInput::make('CurrentEmployer')
                                    ->label('Current Employer (Company Name)'),
                                Forms\Components\TextInput::make('CurrentJobTitle')
                                    ->label('Current Job Title'),
                                Forms\Components\TextInput::make('CurrentSalary')
                                    ->label('Current Salary'),
                            ]),
                        Tabs\Tab::make('Address Information')
                            ->icon('heroicon-o-map-pin')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('Street'),
                                Forms\Components\TextInput::make('City'),
                                Forms\Components\TextInput::make('Country'),
                                Forms\Components\TextInput::make('ZipCode'),
                                Forms\Components\TextInput::make('State'),
                            ]),
                        Tabs\Tab::make('Academic Information')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Repeater::make('School')
                                    ->label('')
                                    ->addActionLabel('Add Degree Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('school_name')
                                            ->required(),
                                        Forms\Components\TextInput::make('major')
                                            ->required(),
                                        Forms\Components\Select::make('duration')
                                            ->options([
                                                '4years' => '4 Years',
                                                '5years' => '5 Years',
                                            ])
                                            ->required(),
                                        Forms\Components\Checkbox::make('pursuing')
                                            ->inline(false),
                                    ])
                                    ->deleteAction(
                                        fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                                    )
                                    ->columns(4),
                            ]),
                        Tabs\Tab::make('Curriculum Vitae/Resume')
                            ->icon('heroicon-o-document')
                            ->schema([
                                Forms\Components\FileUpload::make('resumeFile')
                                    ->label('Upload New Resume')
                                    ->directory('temp-resumes')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(2048)
                                    ->downloadable()
                                    ->openable()
                                    ->previewable()
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->resumeFile = $state;
                                    }),
                                    
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('updateResume')
                                        ->label('Update Curriculum Vitae/Resume')
                                        ->color('primary')
                                        ->icon('heroicon-o-arrow-up-tray')
                                        ->action('updateResume')
                                        ->hidden(fn (): bool => empty($this->resumeFile))
                                ]),
                                
                                Forms\Components\View::make('filament.candidate.resume-viewer')
                                    ->visible(fn (): bool => $this->resumeUrl !== null)
                            ])
                    ])
            ])
            ->statePath('data');
    }

    protected function hasResume(): bool
    {
        $user = Filament::auth()->user();
        $candidate = Candidates::where('Email', $user->email)->first();
        
        return $candidate && $candidate->resume()->exists();
    }
}