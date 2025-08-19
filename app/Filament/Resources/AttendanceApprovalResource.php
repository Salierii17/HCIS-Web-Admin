<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceApprovalResource\Pages;
use App\Models\AttendanceApproval;
use Carbon\Carbon;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AttendanceApprovalResource extends Resource
{
    protected static ?string $model = AttendanceApproval::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Attendance';

    protected static ?string $modelLabel = 'Attendance Approval';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['requester', 'attendance']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('requester.name')
                    ->label('Employee')
                    ->default(fn (?AttendanceApproval $record): string => $record?->requester?->name ?? '')
                    ->disabled(),
                TextInput::make('attendance.date')
                    ->label('Date')
                    ->default(fn (?AttendanceApproval $record): string => $record?->attendance?->date ?? '')
                    ->disabled(),
                TextInput::make('requested_clock_out_time')
                    ->label('Requested Clock Out')
                    ->disabled(),
                Textarea::make('employee_reason')
                    ->label('Employee Reason')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendance.date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('requested_clock_in_time')
                    ->label('Clock-In Change')
                    ->formatStateUsing(function (?AttendanceApproval $record) {
                        if (! $record || is_null($record->requested_clock_in_time)) {
                            return 'No Change';
                        }
                        $original = $record->attendance->clock_in_time ? Carbon::parse($record->attendance->clock_in_time)->format('H:i') : 'N/A';
                        $requested = Carbon::parse($record->requested_clock_in_time)->format('H:i');

                        return "{$original} → {$requested}";
                    })
                    ->color(fn ($state) => $state === 'No Change' ? 'gray' : 'primary'),

                Tables\Columns\TextColumn::make('requested_clock_out_time')
                    ->label('Clock-Out Change')
                    ->formatStateUsing(function (?AttendanceApproval $record) {
                        if (! $record || is_null($record->requested_clock_out_time)) {
                            return 'No Change'; // Show default text
                        }
                        $original = $record->attendance->clock_out_time ? Carbon::parse($record->attendance->clock_out_time)->format('H:i') : 'N/A';
                        $requested = Carbon::parse($record->requested_clock_out_time)->format('H:i');

                        return "{$original} → {$requested}";
                    })
                    ->color(fn ($state) => $state === 'No Change' ? 'gray' : 'primary'),

                Tables\Columns\TextColumn::make('requestedLocationType.arrangement_type')
                    ->label('Arrangement Change')
                    ->formatStateUsing(function (?AttendanceApproval $record) {
                        if (! $record || is_null($record->requested_location_type_id)) {
                            return 'No Change'; // Show default text
                        }
                        $original = $record->attendance->locationType->arrangement_type ?? 'N/A';
                        $requested = $record->requestedLocationType->arrangement_type;

                        return "{$original} → {$requested}";
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'No Change' ? 'gray' : 'primary'),

                Tables\Columns\TextColumn::make('employee_reason')
                    ->label('Reason')
                    ->limit(40)
                    ->tooltip(fn (AttendanceApproval $record): string => $record->employee_reason),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn (AttendanceApproval $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('manager_comment')
                            ->label('Reason for Approval')
                            ->required(),
                    ])
                    ->action(function (AttendanceApproval $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $attendance = $record->attendance;

                            if ($record->requested_clock_in_time) {
                                $attendance->clock_in_time = $record->requested_clock_in_time;
                            }
                            if ($record->requested_clock_out_time) {
                                $attendance->clock_out_time = $record->requested_clock_out_time;
                            }
                            if ($record->requested_location_type_id) {
                                $attendance->location_type_id = $record->requested_location_type_id;
                            }

                            $attendance->approval_status = 'Verified';
                            $attendance->save();

                            $record->status = 'approved';
                            $record->reviewed_by_id = auth()->id();
                            $record->manager_comment = $data['manager_comment'];
                            $record->save();
                        });

                        Notification::make()
                            ->title('Approved successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn (AttendanceApproval $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('manager_comment')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->action(function (AttendanceApproval $record, array $data) {
                        $attendance = $record->attendance;
                        $attendance->approval_status = 'Rejected';
                        $attendance->save();

                        $record->status = 'rejected';
                        $record->reviewed_by_id = auth()->id();
                        $record->manager_comment = $data['manager_comment'];
                        $record->save();

                        Notification::make()
                            ->title('Rejected successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('manager_comment')
                                ->label('Reason for Approval (Optional)'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            DB::transaction(function () use ($records, $data) {
                                foreach ($records as $record) {
                                    $attendance = $record->attendance;
                                    if ($record->requested_clock_in_time) {
                                        $attendance->clock_in_time = $record->requested_clock_in_time;
                                    }
                                    if ($record->requested_clock_out_time) {
                                        $attendance->clock_out_time = $record->requested_clock_out_time;
                                    }
                                    if ($record->requested_location_type_id) {
                                        $attendance->location_type_id = $record->requested_location_type_id;
                                    }
                                    $attendance->approval_status = 'Verified';
                                    $attendance->save();

                                    $record->status = 'approved';
                                    $record->reviewed_by_id = auth()->id();
                                    $record->manager_comment = $data['manager_comment'];
                                    $record->save();
                                }
                            });
                            Notification::make()
                                ->title('Approved ' . $records->count() . ' records successfully')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('manager_comment')
                                ->label('Reason for Rejection')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            DB::transaction(function () use ($records, $data) {
                                foreach ($records as $record) {
                                    $record->attendance->update(['approval_status' => 'Rejected']);
                                    $record->update([
                                        'status' => 'rejected',
                                        'reviewed_by_id' => auth()->id(),
                                        'manager_comment' => $data['manager_comment'],
                                    ]);
                                }
                            });
                            Notification::make()
                                ->title('Rejected ' . $records->count() . ' records successfully')
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListAttendanceApprovals::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : 'success';
    }
}
