<?php

namespace App\Filament\Resources;

use App\Filament\Enums\JobCandidateStatus;
use App\Filament\Resources\JobCandidatesResource\Pages;
use App\Filament\Resources\JobCandidatesResource\RelationManagers;
use App\Models\Candidates;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use App\Models\User;
use App\Notifications\User\InviteNewSystemUserNotification;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class JobCandidatesResource extends Resource
{
    protected static ?string $model = JobCandidates::class;

    protected static ?string $recordTitleAttribute = 'job.JobTitle';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'vaadin-diploma-scroll';

    // Add this method to the JobCandidatesResource class
    protected static function generateDocumentNumber(): string
    {
        return now()->format('m/d/Y H:i:s');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                array_merge(
                    [],
                    self::candidatePipelineFormLayout(),
                    self::candidateBasicInformationFormLayout(),
                    self::candidateCurrentJobInformationFormLayout(),
                    self::candidateAddressInformationFormLayout()
                )
            );
    }

    public static function candidatePipelineFormLayout(): array
    {
        return [
            Forms\Components\Section::make('Candidate Pipeline')
                ->schema([
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\Select::make('JobId')
                                ->label('Job Associated')
                                ->options(JobOpenings::all()->pluck('postingTitle', 'id'))
                                ->required()
                                ->columnSpan(1),

                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\Select::make('CandidateStatus')
                                        ->label('Status')
                                        ->options(JobCandidateStatus::class)
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $record) {
                                            if ($record->exists) {
                                                session()->put('status_changed_'.$record->id, true);
                                                session()->put('email_sent_'.$record->id, false);
                                            }
                                        })
                                        ->columnSpan([
                                            'default' => 2,
                                            'lg' => fn ($get, $livewire) => $livewire instanceof \App\Filament\Resources\JobCandidatesResource\Pages\EditJobCandidates ? 3 : 2,
                                        ]),

                                    // Forms\Components\Actions::make([
                                    //     Forms\Components\Actions\Action::make('sendEmail')
                                    //         ->icon('heroicon-o-paper-airplane')
                                    //         ->color(function ($record) {
                                    //             return session()->get('email_sent_' . $record->id, false) ? 'gray' : 'success';
                                    //         })
                                    //         ->disabled(function ($record) {
                                    //             return session()->get('email_sent_' . $record->id, false);
                                    //         })
                                    //         ->tooltip(function ($record) {
                                    //             return session()->get('email_sent_' . $record->id, false)
                                    //                 ? 'Email already sent'
                                    //                 : 'Send Status Email';
                                    //         })
                                    //         ->modalHeading(fn ($record) => "Send {$record->CandidateStatus} Notification")
                                    //         ->form(function ($record) {
                                    //             return [
                                    //                 Forms\Components\Hidden::make('CandidateStatus')
                                    //                     ->default($record->CandidateStatus),
                                    //                 Forms\Components\Hidden::make('record')
                                    //                     ->default($record),
                                    //                 ...self::getEmailFormSchema()
                                    //             ];
                                    //         })
                                    //         ->action(function ($record, $data) {
                                    //             self::handleEmailSend($record, $data);
                                    //         })
                                    // ])
                                    // ->columnSpan(1)
                                    // ->alignCenter()
                                    // ->extraAttributes(['style' => 'margin-top: 30px;'])

                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('sendEmail')
                                            ->icon('heroicon-o-paper-airplane')
                                            ->color('success')
                                            ->tooltip('Send Status Email')
                                            ->modalHeading(fn ($record) => "Send {$record->CandidateStatus} Notification")
                                            ->closeModalByClickingAway(false) // Prevent closing by clicking outside
                                            ->form(function ($record) {
                                                return [
                                                    Forms\Components\Hidden::make('CandidateStatus')
                                                        ->default($record->CandidateStatus),
                                                    Forms\Components\Hidden::make('record')
                                                        ->default($record),
                                                    ...self::getEmailFormSchema(),
                                                ];
                                            })
                                            ->action(function ($record, $data) {
                                                self::handleEmailSend($record, $data);
                                            })
                                            ->visible(function ($livewire) {
                                                return ! ($livewire instanceof \App\Filament\Resources\JobCandidatesResource\Pages\EditJobCandidates);
                                            }),
                                    ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'lg' => fn ($get, $livewire) => $livewire instanceof \App\Filament\Resources\JobCandidatesResource\Pages\EditJobCandidates ? 0 : 1,
                                        ])
                                        ->alignCenter()
                                        ->extraAttributes(['style' => 'margin-top: 30px;']),
                                ])
                                ->columns(3) // Total of 3 columns for the inner grid
                                ->columnSpan(1),
                        ])
                        ->columns(2),

                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('CandidateSource')
                                ->nullable()
                                ->default('web')
                                ->columnSpan(1),
                            Forms\Components\Select::make('CandidateOwner')
                                ->label('Candidate Owner')
                                ->options(User::all()->pluck('name', 'id'))
                                ->nullable()
                                ->columnSpan(1),
                        ])
                        ->columns(2),
                ]),
        ];
    }

    // public static function candidatePipelineFormLayout(): array
    // {
    //     return [
    //         Forms\Components\Section::make('Candidate Pipeline')
    //             ->schema([
    //                 Forms\Components\Grid::make()
    //                     ->schema([
    //                         Forms\Components\Select::make('JobId')
    //                             ->label('Job Associated')
    //                             ->options(JobOpenings::all()->pluck('JobTitle', 'id'))
    //                             ->required()
    //                             ->columnSpan(1),

    //                         Forms\Components\Grid::make()
    //                             ->columns(2)
    //                             ->schema([
    //                                 Forms\Components\Select::make('CandidateStatus')
    //                                     ->label('Status')
    //                                     ->options(JobCandidateStatus::class)
    //                                     ->required()
    //                                     ->live()
    //                                     ->afterStateUpdated(function ($state, $record) {
    //                                         if ($record->exists) {
    //                                             session()->put('status_changed_' . $record->id, true);
    //                                             session()->put('email_sent_' . $record->id, false);
    //                                         }
    //                                     }),

    //                                 Forms\Components\Actions::make([
    //                                     Forms\Components\Actions\Action::make('sendEmail')
    //                                         ->icon('heroicon-o-paper-airplane')
    //                                         ->color('primary')
    //                                         ->tooltip('Send Status Email')
    //                                         ->modalHeading(fn ($record) => "Send {$record->CandidateStatus} Notification")
    //                                         ->form(function ($record) {
    //                                             return [
    //                                                 Forms\Components\Hidden::make('CandidateStatus')
    //                                                     ->default($record->CandidateStatus),

    //                                                 Forms\Components\Hidden::make('record')
    //                                                     ->default($record),

    //                                                 ...self::getEmailFormSchema()
    //                                             ];
    //                                         })
    //                                         ->action(function ($record, $data) {
    //                                             self::handleEmailSend($record, $data);
    //                                         })
    //                                 ])
    //                                 ->alignCenter()
    //                                 ->extraAttributes(['style' => 'margin-top: 30px;']) // Adjust to align vertically
    //                             ])
    //                             ->columnSpan(1),
    //                     ])
    //                     ->columns(2),

    //                 Forms\Components\Grid::make()
    //                     ->schema([
    //                         Forms\Components\TextInput::make('CandidateSource')
    //                             ->nullable()
    //                             ->default('web')
    //                             ->columnSpan(1),
    //                         Forms\Components\Select::make('CandidateOwner')
    //                             ->label('Candidate Owner')
    //                             ->options(User::all()->pluck('name', 'id'))
    //                             ->nullable()
    //                             ->columnSpan(1),
    //                     ])
    //                     ->columns(2),
    //             ]),
    //     ];
    // }

    // public static function candidatePipelineFormLayout(): array
    // {
    //     return [
    //         Forms\Components\Section::make('Candidate Pipeline')
    //             ->schema([
    //                 Forms\Components\Grid::make()
    //                     ->schema([
    //                         Forms\Components\Select::make('JobId')
    //                             ->label('Job Associated')
    //                             ->options(JobOpenings::all()->pluck('JobTitle', 'id'))
    //                             ->required()
    //                             ->columnSpan(1),

    //                         Forms\Components\Group::make()
    //                             ->schema([
    //                                 Forms\Components\Select::make('CandidateStatus')
    //                                     ->label('Candidate Status')
    //                                     ->options(JobCandidateStatus::class)
    //                                     ->required()
    //                                     ->live()
    //                                     ->afterStateUpdated(function ($state, $record) {
    //                                         if ($record->exists) {
    //                                             session()->put('status_changed_' . $record->id, true);
    //                                             session()->put('email_sent_' . $record->id, false);
    //                                         }
    //                                     }),
    //                                 Forms\Components\Actions::make([
    //                                     Forms\Components\Actions\Action::make('sendEmail')
    //                                         ->label('Send Email')
    //                                         ->icon('heroicon-o-envelope')
    //                                         ->modalHeading(fn ($record) => "Send {$record->CandidateStatus} Notification")
    //                                         ->form(function ($record) {
    //                                             return [
    //                                                 Forms\Components\Hidden::make('CandidateStatus')
    //                                                     ->default($record->CandidateStatus),

    //                                                 Forms\Components\Hidden::make('record')
    //                                                     ->default($record),

    //                                                 ...self::getEmailFormSchema()
    //                                             ];
    //                                         })
    //                                         ->action(function ($record, $data) {
    //                                             self::handleEmailSend($record, $data);
    //                                         })
    //                                 ])
    //                             ])
    //                             ->columnSpan(1),
    //                     ])
    //                     ->columns(2),

    //                 Forms\Components\Grid::make()
    //                     ->schema([
    //                         Forms\Components\TextInput::make('CandidateSource')
    //                             ->nullable()
    //                             ->default('web')
    //                             ->columnSpan(1),
    //                         Forms\Components\Select::make('CandidateOwner')
    //                             ->label('Candidate Owner')
    //                             ->options(User::all()->pluck('name', 'id'))
    //                             ->nullable()
    //                             ->columnSpan(1),
    //                     ])
    //                     ->columns(2),
    //             ]),
    //     ];
    // }

    protected static function getEmailFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Email Content')
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->required()
                        ->default(function ($get) {
                            $status = $get('CandidateStatus');
                            $position = $get('record.job.JobTitle') ?? 'Position';

                            return match ($status) {
                                'Interview-Scheduled', 'Interview-to-be-Scheduled' => "Interview Invitation: {$position}",
                                'Offer-Made' => "Exciting News: Job Offer for {$position}!",
                                'Hired' => 'Welcome to Our Team!',
                                'Rejected', 'Rejected-by-Hiring-Manager' => 'Update on Your Application',
                                default => 'Update Regarding Your Application'
                            };
                        }),

                    Forms\Components\Textarea::make('note')
                        ->label('Additional Notes')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Appointment Details')
                ->schema([
                    Forms\Components\Fieldset::make('Interview Date & Time')
                        ->schema([
                            Forms\Components\DatePicker::make('interview_date')
                                ->label('Date')
                                ->required(),

                            DateTimePicker::make('interview_time')
                                ->label('Interview Time')
                                ->required()
                                ->default(now()->setTime(8, 0)) // Default to 08:00 AM
                                ->displayFormat('h:i A') // Show as 12-hour format
                                ->seconds(false) // Hide seconds
                                ->minutesStep(15) // 15-minute increments
                                ->withoutDate(), // Show only time picker

                            // Forms\Components\TextInput::make('interview_time')
                            //     ->label('Time (e.g. 10:00 AM)')
                            //     ->required()
                            //     ->default('08:00 AM')
                            //     ->rule('regex:/^(0[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$/i'),
                        ])
                        ->columnSpanFull(),

                    Forms\Components\Select::make('interview_type')
                        ->label('Interview Type')
                        ->options([
                            'online' => 'Virtual',
                            'offline' => 'In-person',
                        ])
                        ->default('online')
                        ->live()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('meeting_link')
                        ->label('Meeting Link')
                        ->url()
                        ->required()
                        ->visible(
                            fn ($get) => in_array($get('CandidateStatus'), [
                                'Interview-Scheduled',
                                'Interview-to-be-Scheduled',
                            ]) &&
                            $get('interview_type') === 'online'
                        )
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('location')
                        ->label('Location')
                        ->required()
                        ->visible(
                            fn ($get) => in_array($get('CandidateStatus'), [
                                'Interview-Scheduled',
                                'Interview-to-be-Scheduled',
                            ]) &&
                            $get('interview_type') === 'offline'
                        )
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('interviewer_name')
                        ->label('Interviewer Name')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('interview_duration')
                        ->label('Duration (minutes)')
                        ->numeric()
                        ->default(60)
                        ->suffix('minutes')
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->visible(fn ($get) => in_array($get('CandidateStatus'), [
                    'Interview-Scheduled',
                    'Interview-to-be-Scheduled',
                ])),

            Forms\Components\Section::make('Offer Details')
                ->visible(fn ($get) => $get('CandidateStatus') === 'Offer-Made')
                ->schema([
                    Forms\Components\FileUpload::make('attachments')
                        ->label('Offer Documents')
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf'])
                        ->directory(fn () => 'offer-documents/'.now()->format('m-d-Y_H-i-s'))
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('response_deadline')
                        ->label('Response Deadline')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('offer_details')
                        ->label('Compensation Details')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Onboarding Information')
                ->visible(fn ($get) => $get('CandidateStatus') === 'Hired')
                ->schema([
                    Forms\Components\TextInput::make('onboarding_location')
                        ->label('Onboarding Location')
                        ->required(),
                    DateTimePicker::make('onboarding_time')
                        ->label('Onboarding Time')
                        ->required()
                        ->default(now()->setTime(9, 0)) // Default to 09:00
                        ->displayFormat('h:i A') // Show as 12-hour format
                        ->seconds(false) // Hide seconds
                        ->minutesStep(15) // Optional: 15-minute increments
                        ->withoutDate(), // Show only time picker
                    // Forms\Components\TextInput::make('onboarding_time')
                    //     ->label('Onboarding Time (e.g. 09:00 AM)')
                    //     ->required()
                    //     ->default('09:00 AM')
                    //     ->rule('regex:/^(0[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$/i'),
                    // Forms\Components\Textarea::make('onboarding_instructions')
                    //     ->label('Instructions')
                    //     ->columnSpanFull(),
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required()
                        ->columnSpan(1),
                ])
                ->columns(2),

            Forms\Components\Section::make('Feedback')
                ->visible(fn ($get) => in_array($get('CandidateStatus'), [
                    'Rejected',
                    'Rejected-by-Hiring-Manager',
                ]))
                ->schema([
                    Forms\Components\Textarea::make('feedback')
                        ->label('Constructive Feedback')
                        ->columnSpanFull(),
                ]),
        ];
    }

    // protected static function getEmailFormSchema(): array
    // {
    //     return [
    //         Forms\Components\Section::make('Email Content')
    //             ->schema([
    //                 Forms\Components\TextInput::make('subject')
    //                     ->required()
    //                     ->default(fn ($get) => match($get('CandidateStatus')) {
    //                         'Interview-Scheduled', 'Interview-to-be-Scheduled' => 'Interview Invitation: ' . ($get('record.job.JobTitle') ?? 'Position'),
    //                         'Offer-Made' => 'Job Offer: ' . ($get('record.job.JobTitle') ?? 'Position'),
    //                         'Hired' => 'Welcome to Our Team!',
    //                         'Rejected', 'Rejected-by-Hiring-Manager' => 'Update on Your Application',
    //                         default => 'Application Status Update'
    //                     }),

    //                 Forms\Components\Textarea::make('note')
    //                     ->label('Personal Message')
    //                     ->placeholder('Add a personalized note...')
    //                     ->columnSpanFull(),

    //                 // Common fields
    //                 Forms\Components\TextInput::make('position_name')
    //                     ->label('Position Title')
    //                     ->default(fn ($get) => $get('record.job.JobTitle'))
    //                     ->required()
    //                     ->visible(fn ($get) => in_array($get('CandidateStatus'), [
    //                         'Interview-Scheduled', 'Interview-to-be-Scheduled',
    //                         'Offer-Made', 'Hired'
    //                     ])),

    //                 // Interview fields
    //                 Forms\Components\Fieldset::make('Interview Details')
    //                     ->visible(fn ($get) => in_array($get('CandidateStatus'), [
    //                         'Interview-Scheduled', 'Interview-to-be-Scheduled'
    //                     ]))
    //                     ->schema([
    //                         Forms\Components\Fieldset::make('Date & Time')
    //                             ->schema([
    //                                 Forms\Components\Grid::make(2)
    //                                     ->schema([
    //                                         Forms\Components\DatePicker::make('interview_date')
    //                                             ->label('Date')
    //                                             ->required()
    //                                             ->columnSpan(1),

    //                                         \App\Filament\Forms\Components\AnalogTimePicker::make('interview_time')
    //                                             ->label('Time')
    //                                             ->required()
    //                                             ->columnSpan(1),
    //                                     ])
    //                                     ->columns(2),
    //                             ])
    //                             ->columnSpanFull(),

    //                         Forms\Components\TextInput::make('interviewer_name')
    //                             ->required(),

    //                         Forms\Components\TextInput::make('interview_duration')
    //                             ->numeric()
    //                             ->suffix('minutes')
    //                             ->default(45),

    //                         Forms\Components\Select::make('interview_type')
    //                             ->options([
    //                                 'virtual' => 'Virtual',
    //                                 'in-person' => 'In-Person'
    //                             ])
    //                             ->default('virtual') // Set default to virtual
    //                             ->live()
    //                             ->required()
    //                             ->columnSpan(1),

    //                         Forms\Components\TextInput::make('meeting_link')
    //                             ->visible(fn ($get) => $get('interview_type') === 'virtual')
    //                             ->url()
    //                             ->columnSpan(1),

    //                         Forms\Components\TextInput::make('location')
    //                             ->visible(fn ($get) => $get('interview_type') === 'in-person')
    //                             ->columnSpan(1),

    //                         // Forms\Components\Select::make('interview_type')
    //                         //     ->label('Interview Type')
    //                         //     ->options([
    //                         //         'online' => 'Online',
    //                         //         'offline' => 'Offline',
    //                         //     ])
    //                         //     ->default('online') // Set default to online
    //                         //     ->live()
    //                         //     ->required()
    //                         //     ->columnSpan(1),

    //                         // Forms\Components\TextInput::make('meeting_link')
    //                         //     ->label('Meeting Link')
    //                         //     ->url()
    //                         //     ->required()
    //                         //     //->default('https://meet.google.com/new') // Optional: Set default meeting link
    //                         //     ->visible(fn ($get) =>
    //                         //         in_array($get('CandidateStatus'), [
    //                         //             'Interview-Scheduled',
    //                         //             'Interview-to-be-Scheduled'
    //                         //         ]) &&
    //                         //         ($get('interview_type') === 'online' || $get('interview_type') === null) // Show if online or null
    //                         //     )
    //                         //     ->columnSpan(1),

    //                         // Forms\Components\TextInput::make('location')
    //                         //     ->label('Location')
    //                         //     ->required()
    //                         //     ->visible(fn ($get) =>
    //                         //         in_array($get('CandidateStatus'), [
    //                         //             'Interview-Scheduled',
    //                         //             'Interview-to-be-Scheduled'
    //                         //         ]) &&
    //                         //         $get('interview_type') === 'offline'
    //                         //     )
    //                         //     ->columnSpan(1),
    //                     ]),

    //                 // Offer fields
    //                 Forms\Components\Fieldset::make('Offer Details')
    //                     ->visible(fn ($get) => $get('CandidateStatus') === 'Offer-Made')
    //                     ->schema([
    //                         Forms\Components\Textarea::make('offer_details')
    //                             ->required()
    //                             ->columnSpanFull(),

    //                         Forms\Components\DatePicker::make('response_deadline')
    //                             ->required(),

    //                         Forms\Components\FileUpload::make('offer_letter')
    //                             ->acceptedFileTypes(['application/pdf'])
    //                             ->downloadable()
    //                     ]),

    //                 // Rejection fields
    //                 Forms\Components\Fieldset::make('Feedback')
    //                     ->visible(fn ($get) => in_array($get('CandidateStatus'), [
    //                         'Rejected', 'Rejected-by-Hiring-Manager'
    //                     ]))
    //                     ->schema([
    //                         Forms\Components\Textarea::make('feedback')
    //                             ->label('Constructive Feedback')
    //                             ->columnSpanFull()
    //                     ]),

    //                 // Onboarding fields
    //                 Forms\Components\Fieldset::make('Onboarding Information')
    //                     ->visible(fn ($get) => $get('CandidateStatus') === 'Hired')
    //                     ->schema([
    //                         Forms\Components\DatePicker::make('start_date')
    //                             ->required(),

    //                         Forms\Components\Textarea::make('onboarding_instructions')
    //                             ->columnSpanFull()
    //                     ]),
    //             ])
    //     ];
    // }

    // protected static function getEmailFormSchema(): array
    // {
    //     return [
    //         Forms\Components\Section::make('Email Content')
    //             ->schema([
    //                 Forms\Components\TextInput::make('subject')
    //                     ->required()
    //                     ->default(function ($get) {
    //                         return "Update Regarding Your Application: {$get('CandidateStatus')}";
    //                     })
    //                     ->columnSpanFull(),

    //                 Forms\Components\Textarea::make('note')
    //                     //->required()
    //                     ->default(function ($get) {
    //                         //$name = $get('record.candidateProfile.full_name') ?? 'Candidate';
    //                         //return "Dear {$name},\n\n";
    //                     })
    //                     ->columnSpanFull(),
    //             ]),

    //         // Dynamic fields section
    //         Forms\Components\Section::make('Appointment Details')
    //             ->schema([
    //                 Forms\Components\Fieldset::make('Interview Date & Time')
    //                     ->schema([
    //                         Forms\Components\Grid::make(2)
    //                             ->schema([
    //                                 Forms\Components\DatePicker::make('interview_date')
    //                                     ->label('Date')
    //                                     ->required()
    //                                     ->columnSpan(1),

    //                                 \App\Filament\Forms\Components\AnalogTimePicker::make('interview_time')
    //                                     ->label('Time')
    //                                     ->required()
    //                                     ->columnSpan(1),
    //                             ])
    //                             ->columns(2),
    //                     ])
    //                     ->columnSpanFull(),

    //                 // Forms\Components\Fieldset::make('Interview Date & Time')
    //                 //     ->schema([
    //                 //         Forms\Components\Grid::make(2)
    //                 //             ->schema([
    //                 //                 Forms\Components\DatePicker::make('interview_date')
    //                 //                     ->label('Date')
    //                 //                     ->required(),

    //                 //                 \App\Filament\Forms\Components\AnalogTimePicker::make('interview_time')
    //                 //                     ->label('Time')
    //                 //                     ->required(),
    //                 //             ]),
    //                 //     ])
    //                 //     ->columnSpanFull()
    //                 //     ->extraAttributes(['class' => '!pt-2']), // Added padding to better align fields

    //                 // Forms\Components\Fieldset::make('Interview Date & Time')
    //                 //     ->schema([
    //                 //         Forms\Components\Grid::make(2) // This creates a 2-column layout
    //                 //             ->schema([
    //                 //                 Forms\Components\DatePicker::make('interview_date')
    //                 //                     ->label('Date')
    //                 //                     ->required(),

    //                 //                 \App\Filament\Forms\Components\AnalogTimePicker::make('interview_time')
    //                 //                     ->label('Time')
    //                 //                     ->required(),
    //                 //             ]),
    //                 //     ])
    //                 //     ->columnSpanFull(),

    //                 // Forms\Components\Fieldset::make('Interview Date & Time')
    //                 //     ->schema([
    //                 //         Forms\Components\DatePicker::make('interview_date')
    //                 //             ->label('Date')
    //                 //             ->required(),

    //                 //         \App\Filament\Forms\Components\AnalogTimePicker::make('interview_time')
    //                 //             ->label('Time')
    //                 //             ->required(),
    //                 //     ])
    //                 //     ->columnSpanFull(),

    //                 Forms\Components\Select::make('interview_type')
    //                     ->label('Interview Type')
    //                     ->options([
    //                         'online' => 'Online',
    //                         'offline' => 'Offline',
    //                     ])
    //                     ->default('online') // Set default to online
    //                     ->live()
    //                     ->required()
    //                     ->columnSpan(1),

    //                 Forms\Components\TextInput::make('meeting_link')
    //                     ->label('Meeting Link')
    //                     ->url()
    //                     ->required()
    //                     //->default('https://meet.google.com/new') // Optional: Set default meeting link
    //                     ->visible(fn ($get) =>
    //                         in_array($get('CandidateStatus'), [
    //                             'Interview-Scheduled',
    //                             'Interview-to-be-Scheduled'
    //                         ]) &&
    //                         ($get('interview_type') === 'online' || $get('interview_type') === null) // Show if online or null
    //                     )
    //                     ->columnSpan(1),

    //                 Forms\Components\TextInput::make('location')
    //                     ->label('Location')
    //                     ->required()
    //                     ->visible(fn ($get) =>
    //                         in_array($get('CandidateStatus'), [
    //                             'Interview-Scheduled',
    //                             'Interview-to-be-Scheduled'
    //                         ]) &&
    //                         $get('interview_type') === 'offline'
    //                     )
    //                     ->columnSpan(1),

    //                 Forms\Components\TextInput::make('interviewer_name')
    //                     ->label('Interviewer Name')
    //                     ->required()
    //                     ->columnSpan(1),

    //                 Forms\Components\TextInput::make('interview_duration')
    //                     ->label('Duration (minutes)')
    //                     ->numeric()
    //                     ->default(60)
    //                     ->columnSpan(1),
    //             ])
    //             ->columns(2)
    //             ->visible(fn ($get) => in_array($get('CandidateStatus'), [
    //                 'Interview-Scheduled',
    //                 'Interview-to-be-Scheduled'
    //             ])),

    //         Forms\Components\Section::make('Offer Details')
    //             ->visible(fn ($get) => $get('CandidateStatus') === 'Offer-Made')
    //             ->schema([
    //                 Forms\Components\FileUpload::make('offer_letter')
    //                     ->label('Offer Letter PDF')
    //                     ->acceptedFileTypes(['application/pdf'])
    //                     ->directory('offer-letters')
    //                     ->preserveFilenames()
    //                     ->downloadable()
    //                     ->openable()
    //                     ->columnSpanFull(),

    //                 Forms\Components\DatePicker::make('response_deadline')
    //                     ->label('Response Deadline')
    //                     ->required()
    //                     ->columnSpan(1),
    //             ])
    //             ->columns(2)
    //             ->collapsible(),

    //         Forms\Components\Section::make('Feedback')
    //             ->visible(fn ($get) => in_array($get('CandidateStatus'), [
    //                 'Rejected',
    //                 'Rejected-by-Hiring-Manager'
    //             ]))
    //             ->schema([
    //                 Forms\Components\Textarea::make('feedback')
    //                     ->label('Constructive Feedback')
    //                     ->columnSpanFull(),
    //             ])
    //             ->collapsible(),

    //         Forms\Components\Section::make('Onboarding Information')
    //             ->visible(fn ($get) => $get('CandidateStatus') === 'Hired')
    //             ->schema([
    //                 Forms\Components\Textarea::make('onboarding_instructions')
    //                     ->label('Instructions')
    //                     ->columnSpanFull(),

    //                 Forms\Components\DatePicker::make('start_date')
    //                     ->label('Start Date')
    //                     ->columnSpan(1),
    //             ])
    //             ->columns(2)
    //             ->collapsible(),
    //     ];
    // }

    protected static function handleEmailSend($record, $data): void
    {
        try {
            $emailContent = [
                'subject' => $data['subject'],
                'candidate_name' => $record->candidateProfile->full_name ?? 'Candidate',
                'status' => $record->CandidateStatus,
                'position_name' => $record->job->postingTitle ?? 'the position',
            ];

            // Add note if provided
            if (! empty($data['note'])) {
                $emailContent['note'] = strip_tags($data['note']); // Remove HTML tags
            }

            // Status-specific data
            switch ($record->CandidateStatus) {
                case 'Interview-Scheduled':
                case 'Interview-to-be-Scheduled':
                    // Ensure interview_time is a DateTime object
                    $interviewTime = \Carbon\Carbon::parse($data['interview_time']);

                    $emailContent['interview_time'] = $interviewTime->format('h:i A');
                    $emailContent['interview_date'] = \Carbon\Carbon::parse($data['interview_date'])
                        ->setTime(
                            $interviewTime->hour,
                            $interviewTime->minute
                        )
                        ->format('l, F j, Y');

                    // Get the raw time input
                    // $timeString = $data['interview_time'];

                    // Validate format (client-side should prevent invalid formats)
                    // if (!preg_match('/^(0[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$/i', $timeString)) {
                    //     throw new \Exception("Invalid time format");
                    // }

                    $emailContent['interviewer_name'] = $data['interviewer_name'] ?? 'Our Hiring Team';
                    $emailContent['interview_duration'] = $data['interview_duration'] ?? '60';

                    if ($data['interview_type'] === 'online') {
                        $emailContent['meeting_details'] = [
                            'type' => 'link',
                            'value' => $data['meeting_link'],
                        ];
                    } else {
                        $emailContent['meeting_details'] = [
                            'type' => 'location',
                            'value' => $data['location'],
                        ];
                    }
                    break;

                    // case 'Interview-Scheduled':
                    // case 'Interview-to-be-Scheduled':
                    //     $date = \Carbon\Carbon::parse($data['interview_date']);
                    //     $timeParts = explode(':', $data['interview_time']);
                    //     $date->setTime($timeParts[0], $timeParts[1]);

                    //     // Format date without time
                    //     $emailContent['interview_date'] = $date->format('l, F j, Y');
                    //     // Keep time separate
                    //     $emailContent['interview_time'] = $date->format('g:i A');
                    //     $emailContent['interviewer_name'] = $data['interviewer_name'] ?? 'Our Hiring Team';
                    //     $emailContent['interview_duration'] = $data['interview_duration'] ?? '60';

                    //     // Add meeting link or location based on interview type
                    //     if ($data['interview_type'] === 'online') {
                    //         $emailContent['meeting_details'] = [
                    //             'type' => 'link',
                    //             'value' => $data['meeting_link']
                    //         ];
                    //     } else {
                    //         $emailContent['meeting_details'] = [
                    //             'type' => 'location',
                    //             'value' => $data['location']
                    //         ];
                    //     }
                    //     break;

                    // case 'Interview-Scheduled':
                    // case 'Interview-to-be-Scheduled':
                    //     $date = \Carbon\Carbon::parse($data['interview_date']);
                    //     $timeParts = explode(':', $data['interview_time']);
                    //     $date->setTime($timeParts[0], $timeParts[1]);

                    //     $emailContent['interview_date'] = $date->format('l, F j, Y \a\t g:i A');
                    //     $emailContent['interview_time'] = $date->format('g:i A');
                    //     $emailContent['interviewer_name'] = $data['interviewer_name'] ?? 'Our Hiring Team';
                    //     $emailContent['interview_duration'] = $data['interview_duration'] ?? '60';
                    //     // Add meeting link or location based on interview type
                    //     if ($data['interview_type'] === 'online') {
                    //         $emailContent['meeting_details'] = [
                    //             'type' => 'link',
                    //             'value' => $data['meeting_link']
                    //         ];
                    //     } else {
                    //         $emailContent['meeting_details'] = [
                    //             'type' => 'location',
                    //             'value' => $data['location']
                    //         ];
                    //     }
                    //     break;

                case 'Offer-Made':
                    $emailContent['offer_details'] = strip_tags($data['offer_details']);
                    $emailContent['response_deadline'] = \Carbon\Carbon::parse($data['response_deadline'])->format('l, F j, Y');

                    // Handle multiple attachments
                    $attachments = [];
                    if (! empty($data['attachments'])) {
                        foreach ($data['attachments'] as $attachment) {
                            $path = storage_path('app/public/'.ltrim($attachment, '/'));
                            if (file_exists($path)) {
                                $attachments[] = [
                                    'path' => $path,
                                    'name' => basename($attachment),
                                ];
                            }
                        }
                    }
                    break;

                case 'Hired':
                    $emailContent['onboarding_instructions'] = $data['onboarding_instructions'] ?? 'Our HR team will contact you shortly';
                    $emailContent['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('l, F j, Y');
                    $emailContent['onboarding_location'] = $data['onboarding_location'];

                    // For DateTimePicker values:
                    $onboardingTime = \Carbon\Carbon::parse($data['onboarding_time']);

                    $emailContent['onboarding_time'] = $onboardingTime->format('h:i A');

                    // $onboardingTime = $data['onboarding_time'];

                    // // Validate format
                    // if (!preg_match('/^(0[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$/i', $onboardingTime)) {
                    //     throw new \Exception("Invalid onboarding time format");
                    // }

                    // $emailContent['onboarding_time'] = $onboardingTime;
                    break;

                    // case 'Hired':
                    //     $emailContent['onboarding_instructions'] = $data['onboarding_instructions'] ?? 'Our HR team will contact you shortly';
                    //     $emailContent['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('l, F j, Y');
                    //     $emailContent['onboarding_location'] = $data['onboarding_location'];
                    //     $emailContent['onboarding_time'] = $data['onboarding_time'];
                    //     break;

                    // case 'Hired':
                    //     $emailContent['onboarding_instructions'] = $data['onboarding_instructions'] ?? 'Our HR team will contact you shortly';
                    //     // Fix: Ensure start_date is a Carbon instance before formatting
                    //     $emailContent['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('l, F j, Y');
                    //     break;

                case 'Rejected':
                case 'Rejected-by-Hiring-Manager':
                    $emailContent['feedback'] = $data['feedback'] ?? 'We appreciate your interest in our company';
                    break;
            }

            // Handle attachments - Fix: Proper path handling for attachments
            $attachmentPath = null;
            $attachmentName = null;

            if ($record->CandidateStatus === 'Offer-Made' && isset($data['offer_letter'])) {
                $attachmentPath = storage_path('app/public/'.ltrim($data['offer_letter'], '/'));
                $attachmentName = basename($data['offer_letter']);

                // Verify the file exists before attaching
                if (! file_exists($attachmentPath)) {
                    throw new \Exception("Offer letter file not found at: {$attachmentPath}");
                }
            }

            // Send notification
            $record->notify(
                new \App\Notifications\Candidates\CandidateStatusUpdateNotification(
                    $emailContent,
                    $attachments ?? []
                )
            );

            // Update session
            session()->put('email_sent_'.$record->id, true);
            session()->put('status_changed_'.$record->id, false);

            Notification::make()
                ->title('Status Email Sent')
                ->body('The candidate has been notified about their application status.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Failed to send status email: '.$e->getMessage());
            Notification::make()
                ->title('Email Failed')
                ->body('Error sending status email: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function candidateBasicInformationFormLayout(): array
    {
        return [
            Forms\Components\TextInput::make('JobCandidateId')
                ->visibleOn('view')
                ->readOnly()
                ->disabled(),
            Forms\Components\Section::make('Candidate Basic Information')
                ->schema([
                    Forms\Components\Select::make('candidate')
                        ->options(Candidates::all()->pluck('full_name', 'id'))
                        ->required(),
                    Forms\Components\TextInput::make('mobile')
                        ->nullable(),
                    Forms\Components\TextInput::make('Email')
                        ->required()
                        ->email(),
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
                ])->columns(2),
        ];
    }

    public static function candidateCurrentJobInformationFormLayout(): array
    {
        return [
            Forms\Components\Section::make('Candidate Current Job Information')
                ->schema([
                    Forms\Components\TextInput::make('CurrentEmployer')
                        ->label('Current Employer (Company Name)'),
                    Forms\Components\TextInput::make('CurrentJobTitle')
                        ->label('Current Job Title'),
                    Forms\Components\TextInput::make('CurrentSalary')
                        ->label('Current Salary'),
                ])->columns(2),
        ];
    }

    public static function candidateAddressInformationFormLayout(): array
    {
        return [
            Forms\Components\Section::make('Candidate Address Information')
                ->schema([
                    Forms\Components\TextInput::make('Street'),
                    Forms\Components\TextInput::make('City'),
                    Forms\Components\TextInput::make('Country'),
                    Forms\Components\TextInput::make('ZipCode'),
                    Forms\Components\TextInput::make('State'),
                ])->columns(2),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidateProfile.full_name')
                    ->label('Candidate Name')
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('candidateProfile', function ($q) use ($search) {
                            $q->where('full_name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('CandidateStatus')
                    ->label('Candidate Status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('CandidateSource')
                    ->label('Candidate Source')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('recordOwner.name')
                    ->label('Candidate Owner')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('mobile')
                    ->label('Mobile')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ExpectedSalary')
                    ->label('Expected Salary')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ExperienceInYears')
                    ->label('Experience In Years')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('HighestQualificationHeld')
                    ->label('Highest Qualification Held')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('CurrentEmployer')
                    ->label('Current Employer Company Name')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('CurrentJobTitle')
                    ->label('Current Job Title')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('CurrentSalary')
                    ->label('Current Salary')
                    ->money(config('recruit.currency_field'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('Street')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('City')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('Country')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ZipCode')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('State')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('addUser')
                    ->label('Add User')
                    ->icon('heroicon-o-user-plus')
                    ->action(function (JobCandidates $record) {
                        try {
                            // Check if user already exists with this email
                            $existingUser = User::where('email', $record->Email)->first();

                            if ($existingUser) {
                                Notification::make()
                                    ->title('User already exists')
                                    ->body('A user with this email already exists in the system.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if (! $record->candidateProfile) {
                                throw new \Exception('Candidate profile not found');
                            }

                            // Create new user
                            $user = User::create([
                                'name' => $record->candidateProfile->full_name,
                                'email' => $record->Email,
                                'password' => Hash::make('password'),
                                'invitation_id' => Str::uuid(),
                                'sent_at' => now(),
                            ]);

                            // Assign Standard role
                            $standardRole = Role::where('name', 'Standard')->first();
                            if ($standardRole) {
                                $user->assignRole($standardRole);
                            }

                            // Send invitation
                            $link = URL::signedRoute('system-user.invite', ['id' => $user->invitation_id]);
                            $user->notify(new InviteNewSystemUserNotification($user, $link));

                            Notification::make()
                                ->title('User created and invited')
                                ->body('The user has been created and an invitation has been sent.')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error creating user')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(function (JobCandidates $record) {
                        return $record->CandidateStatus === 'Joined' && $record->candidateProfile !== null;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('addUsers')
                        ->label('Add Selected as Users')
                        ->icon('heroicon-o-user-plus')
                        ->action(function (Collection $records) {
                            $results = [
                                'created' => 0,
                                'skipped_existing' => 0,
                                'skipped_non_hired' => 0,
                                'skipped_no_profile' => 0,
                                'errors' => [],
                            ];

                            $hiredCandidates = $records->filter(
                                fn ($record) => $record->CandidateStatus === 'Joined'

                            );

                            foreach ($hiredCandidates as $record) {
                                try {
                                    // Skip if no email or profile
                                    if (! $record->Email) {
                                        $results['skipped_no_profile']++;

                                        continue;
                                    }

                                    if (! $record->candidateProfile) {
                                        $results['skipped_no_profile']++;
                                        $results['errors'][] = "No profile for: {$record->Email}";

                                        continue;
                                    }

                                    // Check for existing user
                                    if (User::where('email', $record->Email)->exists()) {
                                        $results['skipped_existing']++;

                                        continue;
                                    }

                                    // Create new user
                                    $user = User::create([
                                        'name' => $record->candidateProfile->full_name,
                                        'email' => $record->Email,
                                        'password' => Hash::make(Str::random(16)),
                                        'invitation_id' => Str::uuid(),
                                        'sent_at' => now(),
                                    ]);

                                    // Assign role
                                    if ($standardRole = Role::where('name', 'Standard')->first()) {
                                        $user->assignRole($standardRole);
                                    }

                                    // Send invitation
                                    $link = URL::signedRoute('system-user.invite', ['id' => $user->invitation_id]);
                                    $user->notify(new InviteNewSystemUserNotification($user, $link));

                                    $results['created']++;

                                } catch (\Exception $e) {
                                    $results['errors'][] = "Error with {$record->Email}: ".$e->getMessage();
                                }
                            }

                            // Non-hired candidates count
                            $results['skipped_non_hired'] = $records->count() - $hiredCandidates->count();

                            // Prepare notification message
                            $messageParts = [];
                            if ($results['created'] > 0) {
                                $messageParts[] = "Created {$results['created']} user(s)";
                            }
                            if ($results['skipped_existing'] > 0) {
                                $messageParts[] = "Skipped {$results['skipped_existing']} existing user(s)";
                            }
                            if ($results['skipped_non_hired'] > 0) {
                                $messageParts[] = "Skipped {$results['skipped_non_hired']} non-hired candidate(s)";
                            }
                            if ($results['skipped_no_profile'] > 0) {
                                $messageParts[] = "Skipped {$results['skipped_no_profile']} candidate(s) with missing profile";
                            }

                            $message = implode('. ', $messageParts).'.';

                            // Show notification
                            $notification = Notification::make()
                                ->title('Bulk User Creation Results')
                                ->body($message)
                                ->success();

                            // Add error details if any
                            if (! empty($results['errors'])) {
                                $notification->actions([
                                    \Filament\Notifications\Actions\Action::make('viewErrors')
                                        ->label('View Errors ('.count($results['errors']).')')
                                        ->color('danger')
                                        ->modalContent(view('filament.user.invitation.bulk-errors', [
                                            'errors' => $results['errors'],
                                        ])),
                                ]);
                            }

                            $notification->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Confirm User Creation')
                        ->modalDescription('Only "Joined" candidates will be processed. Existing users will be skipped.')
                        ->modalSubmitActionLabel('Create Users')
                        ->color('success'),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobCandidates::route('/'),
            'create' => Pages\CreateJobCandidates::route('/create'),
            'view' => Pages\ViewJobCandidates::route('/{record}'),
            'edit' => Pages\EditJobCandidates::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
