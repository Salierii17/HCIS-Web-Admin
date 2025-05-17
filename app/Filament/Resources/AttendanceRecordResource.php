<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceRecordResource\Pages;
use App\Models\Attendance;
use App\Models\WorkArrangement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $modelLabel = 'Attendance Record';

    protected static ?string $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = -2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('date')
                    ->label('Timestamp')
                    ->required(),
                Select::make('employee_id')
                    ->label('Employee Name')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                // Assuming you have a 'department' attribute in your User model
                TextColumn::make('employee.department')
                    ->label('Department')
                    ->searchable(),
                Select::make('location_type_id')
                    ->label('Type/Work Arrangement')
                    ->relationship('locationType', 'name') // Assuming 'name' is the attribute in WorkArrangement
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('check_in_time')
                    ->label('Clock In'),
                DateTimePicker::make('check_out_time')
                    ->label('Clock Out'),
                TextInput::make('gps_coordinates')
                    ->label('GPS Coordinates (Latitude, Longitude)')
                    ->maxLength(255),
                Select::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'status')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('employee.name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('locationType.name') // Display the 'name' from the WorkArrangement model
                    ->label('Work Arrangement')
                    ->searchable(),
                TextColumn::make('check_in_time')
                    ->label('Clock In')
                    ->time()
                    ->sortable(),
                TextColumn::make('check_out_time')
                    ->label('Clock Out')
                    ->time()
                    ->sortable(),
                TextColumn::make('gps_coordinates')
                    ->label('GPS Coordinates'),
                TextColumn::make('status.status')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            // 'view' => Pages\ViewAttendanceRecord::route('/{record}'),
            'edit' => Pages\EditAttendanceRecord::route('/{record}/edit'),
        ];
    }
}