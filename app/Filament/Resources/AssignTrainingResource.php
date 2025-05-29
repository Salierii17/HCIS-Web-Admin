<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignTrainingResource\Pages;
use App\Filament\Resources\AssignTrainingResource\RelationManagers;
use App\Models\AssignTraining;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Models\Package;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use App\Notifications\SendTrainingNotification;

class AssignTrainingResource extends Resource
{
    protected static ?string $model = AssignTraining::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $modelLabel = 'Assign Training';

    protected static ?string $navigationGroup = 'Training';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
{
    return $form->schema([
        Select::make('user_id')
            ->label('User')
            ->relationship('user', 'name')
            ->required(),

        Select::make('package_id')
            ->label('Package')
            ->relationship('package', 'name')
            ->required(),
    ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('package.name')->label('Package'),
                TextColumn::make('created_at')->label('Assigned At')->dateTime(),
            ])
            ->actions([
            Action::make('sendNotification')
                ->label('Send Notification')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Send Notifications?')
                ->modalSubheading(fn (AssignTraining $record) =>
                        'Email Notifications will send to: ' . $record->user->email
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
            ]);
    }

        public static function canViewAny(): bool
        {
            return true;
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
