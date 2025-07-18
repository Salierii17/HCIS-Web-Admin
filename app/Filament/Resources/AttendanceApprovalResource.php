<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceApprovalResource\Pages;
use App\Models\AttendanceApproval;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceApprovalResource extends Resource
{
    protected static ?string $model = AttendanceApproval::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Attendance';

    protected static ?string $modelLabel = 'Attendance Approval';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'pending');
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
                    ->disabled(),
                TextInput::make('attendance.date')
                    ->label('Date')
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
                Tables\Columns\TextColumn::make('attendance.clock_in_time')
                    ->label('Clock In')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('requested_clock_out_time')
                    ->label('Requested Out')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('employee_reason')
                    ->label('Reason')
                    ->limit(40)
                    ->tooltip(fn (AttendanceApproval $record): string => $record->employee_reason),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (AttendanceApproval $record) {
                        $attendance = $record->attendance;
                        $attendance->clock_out_time = $record->requested_clock_out_time;
                        $attendance->approval_status = 'Verified';
                        $attendance->save();

                        $record->status = 'approved';
                        $record->reviewed_by_id = auth()->id();
                        $record->save();

                        Notification::make()
                            ->title('Approved successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
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

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAttendanceApprovals::route('/'),
            'view' => Pages\ViewAttendanceApproval::route('/{record}'),
        ];
    }
}