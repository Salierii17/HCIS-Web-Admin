<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralsResource\Pages;
use App\Models\JobOpenings;
use App\Models\Referrals;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReferralsResource extends Resource
{
    protected static ?string $model = Referrals::class;

    protected static ?string $navigationIcon = 'healthicons-o-referral';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 5;

    public static function getRecordTitle(?Model $record): ?string
    {
        return $record?->candidates?->FirstName;
    }

    public static function getModelLabel(): string
    {
        return __('Referral');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Referrals');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Refer a Candidate')
                    ->schema([
                        Forms\Components\FileUpload::make('resume')
                            ->hint('Supported file types: .pdf')
                            ->nullable()
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('referrals/resumes')
                            ->downloadable()
                            ->openable(),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('viewResume')
                                ->label('View Resume')
                                ->color('success')
                                ->icon('heroicon-o-eye')
                                ->hidden(fn (Forms\Get $get): bool => empty($get('resume')))
                                ->url(fn (Forms\Get $get): string => Storage::url($get('resume')))
                                ->openUrlInNewTab(),

                            Forms\Components\Actions\Action::make('downloadResume')
                                ->label('Download Resume')
                                ->color('primary')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->hidden(fn (Forms\Get $get): bool => empty($get('resume')))
                                ->action(function (Forms\Get $get) {
                                    return response()->download(storage_path('app/public/'.$get('resume')));
                                }),
                        ])->hidden(
                            fn (): bool => ! in_array(\Filament\Support\Enums\ActionSize::tryFrom(request()->route()->getName()) ?? '', ['create', 'edit'])
                        ),

                        Forms\Components\Section::make('Job Recommendation')
                            ->schema([
                            Forms\Components\Select::make('ReferringJob')
                                ->prefixIcon('heroicon-s-briefcase')
                                ->options(JobOpenings::all()->pluck('JobTitle', 'id'))
                                ->required(),
                        ]),
                        Forms\Components\Section::make('Candidate Information')
                            ->schema([
                            Forms\Components\Select::make('Candidate')
                                ->prefixIcon('heroicon-s-briefcase')
                                ->relationship(name: 'candidates', titleAttribute: 'full_name')
                                ->searchable(['email', 'LastName', 'FirstName'])
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('FirstName')
                                        ->label('First Name')
                                        ->required(),
                                    Forms\Components\TextInput::make('LastName')
                                        ->label('Last Name')
                                        ->required(),
                                    Forms\Components\TextInput::make('Mobile')
                                        ->label('Mobile')
                                        ->tel(),
                                    Forms\Components\TextInput::make('email')
                                        ->email()
                                        ->required(),
                                    Forms\Components\TextInput::make('CurrentEmployer')
                                        ->label('Current Employer (Company Name)'),
                                    Forms\Components\TextInput::make('CurrentJobTitle')
                                        ->label('Current Job Title'),
                                ]),
                        ]),
                        Forms\Components\Section::make('Additional Information')
                            ->schema([
                            Forms\Components\Select::make('Relationship')
                                ->options([
                                    'None' => 'None',
                                    'Personally Known' => 'Personally Known',
                                    'Former Colleague' => 'Former Colleague',
                                    'Socially Connected' => 'Socially Connected',
                                    'Got the resume through a common fried' => 'Got the resume through a common fried',
                                    'Others' => 'Others',
                                ]),
                            Forms\Components\Select::make('KnownPeriod')
                                ->options([
                                    'None' => 'None',
                                    'Less than a year',
                                    '1-2 years' => '1-2 years',
                                    '3-5 years' => '3-5 years',
                                    '5+ years' => '5+ years',
                                ]),
                            Forms\Components\Textarea::make('Notes')
                                ->nullable(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidates.full_name')
                    ->label('Candidate Name')
                    ->searchable(['candidates.FirstName', 'candidates.LastName'])
                    ->url(function ($record) {
                        if ($record->candidates) {
                            return \App\Filament\Resources\CandidatesProfileResource::getUrl('view', [
                                'record' => $record->candidates->id,
                            ]);
                        }

                        return null;
                    })
                    ->openUrlInNewTab(false)
                    ->icon(function ($state) {
                        return $state ? 'heroicon-m-arrow-top-right-on-square' : null;
                    })
                    ->iconPosition('after'),

                Tables\Columns\TextColumn::make('jobopenings.JobTitle')
                    ->label('Job Title')
                    ->url(function ($record) {
                        if ($record->jobopenings) {
                            return \App\Filament\Resources\JobOpeningsResource::getUrl('view', [
                                'record' => $record->jobopenings->id,
                            ]);
                        }

                        return null;
                    })
                    ->openUrlInNewTab(false)
                    ->icon(function ($state) {
                        return $state ? 'heroicon-m-arrow-top-right-on-square' : null;
                    })
                    ->iconPosition('after'),

                Tables\Columns\TextColumn::make('jobcandidates.CandidateStatus')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'info',
                        'Contacted' => 'primary',
                        'Qualified' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Status')
                    ->label('Status')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->url(function ($record) {
                        return \App\Filament\Resources\JobCandidatesResource::getUrl('view', [
                            'record' => $record->JobCandidate,
                        ]);
                    })
                    ->hidden(fn ($record): bool => is_null($record->JobCandidate)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferrals::route('/create'),
            'view' => Pages\ViewReferrals::route('/{record}'),
            'edit' => Pages\EditReferrals::route('/{record}/edit'),
        ];
    }
}
