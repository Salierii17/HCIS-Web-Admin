<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignTrainingResource\Pages;
use App\Models\AssignTraining;
use App\Notifications\SendTrainingNotification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignTrainingResource extends Resource
{
    protected static ?string $model = AssignTraining::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = 'Assign Training';

    protected static ?string $navigationGroup = 'Training';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Employee Name')
                ->relationship('user', 'name')
                ->required(),

            Select::make('package_id')
                ->label('Package')
                ->relationship('package', 'name')
                ->required(),

            DateTimePicker::make('deadline')
                ->label('Deadline')
                ->required()
                ->minDate(now()), // agar tidak bisa pilih waktu yang sudah lewat
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Employee Name'),
                TextColumn::make('package.name')->label('Package'),
                TextColumn::make('created_at')->label('Assigned At')->dateTime('d M Y H:i'),
                TextColumn::make('deadline')->label('Deadline')->dateTime('d M Y H:i'),
            ])
            ->actions([
                Action::make('sendNotification')
                    ->label('Send Notification')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Notifications?')
                    ->modalSubheading(
                        fn(AssignTraining $record) => 'Email Notifications will send to: ' . $record->user->email
                    )
                    ->modalButton('Send')
                    ->successNotificationTitle('Done!')
                    ->action(function (AssignTraining $record) {
                        $user = $record->user;
                        $package = $record->package;

                        if ($user && $package) {
                            $user->notify(new SendTrainingNotification($package->name));
                        }
                    }),
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('sendNotification')
                        ->label('Send Notification')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Send Notifications?')
                        ->modalSubheading(function (\Illuminate\Database\Eloquent\Collection $records) {
                            return 'Are you sure you want to send notifications to ' . $records->count() . ' selected users?';
                        })
                        ->modalButton('Send')
                        ->successNotificationTitle('Done!')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            // We loop through each selected record.
                            foreach ($records as $record) {
                                $user = $record->user;
                                $package = $record->package;

                                if ($user && $package) {
                                    $user->notify(new \App\Notifications\SendTrainingNotification($package->name));
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return optional(auth()->user())->can('viewAny', AssignTraining::class);
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
            'index' => Pages\ListAssignTrainings::route('/'),
            'create' => Pages\CreateAssignTraining::route('/create'),
            'edit' => Pages\EditAssignTraining::route('/{record}/edit'),
        ];
    }
}
