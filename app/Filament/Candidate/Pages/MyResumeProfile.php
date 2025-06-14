<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Candidates;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class MyResumeProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected ?string $subheading = 'This profile will be used once you apply for a job in the portal.';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.candidate.pages.my-resume-profile';
    
    public ?array $data = [];

    public function mount(): void
    {
        $user = Filament::auth()->user();
        $profile = $this->getResumeProfile();
        
        // Initialize with default values
        $this->data = [
            'Email' => $user->email ?? '',
            'FirstName' => '',
            'LastName' => '',
            'Mobile' => '',
            'ExperienceInYears' => '',
            'ExpectedSalary' => '',
            'HighestQualificationHeld' => '',
            'CurrentEmployer' => '',
            'CurrentJobTitle' => '',
            'CurrentSalary' => '',
            'Street' => '',
            'City' => '',
            'Country' => '',
            'ZipCode' => '',
            'State' => '',
            'ExperienceDetails' => [],
            'SkillSet' => [],
            'School' => [],
        ];

        // Merge with existing profile data if available
        if ($profile->isNotEmpty()) {
            $profileData = $profile->first()->toArray();
            $this->data = array_merge($this->data, $profileData);
        }

        $this->form->fill($this->data);
    }

    protected function getResumeProfile(): Collection
    {
        $user = Filament::auth()->user();
        
        if (!$user || !$user->email) {
            return new Collection();
        }

        return Candidates::where('Email', $user->email)->get();
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

                    ]),
            ])
            ->statePath('data');
    }
}
