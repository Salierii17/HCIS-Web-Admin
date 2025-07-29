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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class JobCandidatesResource extends Resource
{
    protected static ?string $model = JobCandidates::class;

    protected static ?string $recordTitleAttribute = 'job.postingTitle';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'vaadin-diploma-scroll';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                array_merge([],
                    self::candidatePipelineFormLayout(),
                    self::candidateBasicInformationFormLayout(),
                    self::candidateCurrentJobInformationFormLayout(),
                    self::candidateAddressInformationFormLayout()
                ));
    }

    public static function candidatePipelineFormLayout(): array
    {
        return [
            Forms\Components\Section::make('Candidate Pipeline')
                ->schema([
                    Forms\Components\Select::make('JobId')
                        ->label('Job Associated')
                        ->options(JobOpenings::all()->pluck('JobTitle', 'id'))
                        ->required(),
                    Forms\Components\Select::make('CandidateStatus')
                        ->label('Candidate Status')
                        ->options(JobCandidateStatus::class)
                        ->required(),
                    Forms\Components\TextInput::make('CandidateSource')
                        ->nullable('')
                        ->default('web'),
                    Forms\Components\Select::make('CandidateOwner')
                        ->label('Candidate Owner')
                        ->options(User::all()->pluck('name', 'id'))
                        ->nullable(),
                ])->columns(2),
        ];
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
                        return $record->CandidateStatus === 'Hired' && $record->candidateProfile !== null;
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

                            $hiredCandidates = $records->filter(fn ($record) => $record->CandidateStatus === 'Hired'
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
                        ->modalDescription('Only "Hired" candidates will be processed. Existing users will be skipped.')
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
