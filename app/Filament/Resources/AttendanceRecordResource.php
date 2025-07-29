<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceRecordResource\Pages;
use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\AttendanceStatus;
use App\Models\User;
use App\Models\WorkArrangement;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = 'Attendance Record';

    protected static ?string $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = -2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->relationship('employee', 'name')
                        ->searchable(['name', 'email'])
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')->required(),
                            TextInput::make('email')->email()->required()->unique(table: User::class, column: 'email'),

                        ])
                        ->columnSpan(1),

                    DatePicker::make('date')
                        ->label('Timestamp')
                        ->required()
                        ->native(false)
                        ->default(now())
                        ->columnSpan(1),

                    TimePicker::make('clock_in_time')
                        ->label('Clock In')
                        ->seconds(false)
                        ->nullable()
                        ->columnSpan(1),

                    TimePicker::make('clock_out_time')
                        ->label('Clock Out')
                        ->seconds(false)
                        ->nullable()
                        ->columnSpan(1),

                    Select::make('location_type_id')
                        ->label('Work Arrangement')
                        ->relationship(name: 'locationType', titleAttribute: 'arrangement_type')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->createOptionForm([
                            TextInput::make('arrangement_type')->required()->unique(table: WorkArrangement::class, column: 'arrangement_type'),

                        ])
                        ->columnSpan(1),

                    Select::make('status_id')
                        ->label('Status')
                        ->relationship(name: 'status', titleAttribute: 'status')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('status')->required()->unique(table: AttendanceStatus::class, column: 'status'),
                        ])
                        ->columnSpan(1),

                    TextInput::make('work_hours')
                        ->label('Work Hours (Decimal)')
                        ->numeric()
                        ->nullable()
                        ->step(0.1)
                        ->placeholder('e.g., 8.5 for 8h 30m')
                        ->helperText('Leave blank to auto-calculate from clock in/out if applicable.')
                        ->columnSpan(1),

                    TextInput::make('gps_coordinates')
                        ->label('GPS Coordinates')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpan(1),
                ]),

                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable()
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->datetime()
                    ->label('Timestamp')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('clock_in_time')
                    ->label('Clock In')
                    ->time('H:i')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('clock_out_time')
                    ->label('Clock Out')
                    ->time('H:i')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('formattedWorkDuration')
                    ->label('Work Duration')
                    ->alignCenter()
                    ->placeholder('--:--'),
                TextColumn::make('locationType.arrangement_type')
                    ->label('Arrangement')
                    ->searchable()
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'wfo' => 'success',
                        'wfa' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('gps_coordinates')
                    ->label('GPS')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status.status')
                    ->label('Status')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'present' => 'success',
                        'half day' => 'warning',
                        'absent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('approval_status')
                    ->label('Approval Status')
                    ->badge()
                    ->aligncenter()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'in progress' => 'info',
                        'verified' => 'success',
                        'pending approval' => 'warning',
                        'rejected' => 'danger',
                        'incomplete' => 'gray',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('employee')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Employee'),
                SelectFilter::make('locationType')
                    ->relationship('locationType', 'arrangement_type')
                    ->label('Work Arrangement'),
                SelectFilter::make('status')
                    ->relationship('status', 'status')
                    ->label('Status'),
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')->label('Date From')->native(false),
                        DatePicker::make('created_until')->label('Date Until')->native(false)->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['created_from'] && ! $data['created_until']) {
                            return null;
                        }
                        $parts = [];
                        if ($data['created_from']) {
                            $parts[] = 'From: '.Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until']) {
                            $parts[] = 'Until: '.Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return implode(' ', $parts);
                    }),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Action::make('requestUpdate')
                    ->label('Request Update')
                    ->icon('heroicon-o-plus-circle')
                    ->color('warning')
                // This button will only be visible for records that need fixing
                    ->visible(fn (Attendance $record): bool => in_array($record->approval_status, ['Incomplete', 'Flagged for Review', 'Rejected']))
                // This defines the pop-up form fields
                    ->form([
                        TimePicker::make('requested_clock_out_time')
                            ->required(),
                        Textarea::make('reason')
                            ->label('Reason')
                            ->required(),
                    ])
                // This is the logic that runs when the form is submitted
                    ->action(function (Attendance $record, array $data): void {
                        // 1. Create the approval request
                        AttendanceApproval::create([
                            'attendance_id' => $record->id,
                            'requested_by_id' => auth()->id(), // Logged-in manager is the requester
                            'requested_clock_out_time' => $data['requested_clock_out_time'],
                            'employee_reason' => $data['reason'],
                            'status' => 'pending',
                        ]);

                        // 2. Update the original record's status
                        $record->approval_status = 'Pending Approval';
                        $record->save();

                        Notification::make()
                            ->title('Correction request created successfully')
                            ->success()
                            ->send();
                    }),
                Action::make('approveException')
                    ->label('Approve Exception')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn (Attendance $record): bool => $record->approval_status === 'Flagged for Review')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('manager_comment')
                            ->label('Reason for Approval (e.g., Family Emergency)')
                            ->required(),
                    ])

    // Manager approved fucntion
                    ->action(function (Attendance $record, array $data): void {
                        $record->approval_status = 'Verified';
                        $record->notes = $record->notes."\nException approved by manager: ".$data['manager_comment'];
                        $record->save();

                        Notification::make()
                            ->title('Exception approved successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceRecords::route('/'),
            'create' => Pages\CreateAttendanceRecord::route('/create'),
            'view' => Pages\ViewAttendanceRecord::route('/{record}'),
            'edit' => Pages\EditAttendanceRecord::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['employee.name', 'date', 'locationType.arrangement_type', 'status.status'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
