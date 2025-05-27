<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Profile;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Tapp\FilamentAuthenticationLog\RelationManagers\AuthenticationLogsRelationManager;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationGroup = 'Security & Control';

    protected static ?string $navigationIcon = 'heroicon-c-user-circle';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Grid::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\FileUpload::make('profile_photo_path')
                            ->label('')
                            ->alignCenter()
                            ->avatar()
                            ->visibility('public')
                            ->directory('profile-avatar')
                            ->disk(config('filament.default_filesystem_disk'))
                            ->image(),
                    ]),
                Forms\Components\Section::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->placeholder('John Doe'),
                        Forms\Components\TextInput::make('email')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->email(),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->options(Role::all()->pluck('name', 'id')->toArray())
                            ->preload()
                            ->required()
                            ->multiple()
                            ->searchable(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('email_verified_at'),
                    ])
                    ->columnSpan(['lg' => fn (?User $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (User $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (User $record): ?string => $record->updated_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('joined_at')
                            ->label('Joined at')
                            ->content(fn (User $record): ?string => $record->joined_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?User $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No other system user.')
            ->emptyStateIcon('heroicon-o-user-plus')
            ->emptyStateDescription('Add new user to reflect here.')
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->label('Profile Photo')
                    ->defaultImageUrl(fn (Model $record) => $record->profile_photo_url)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Verified Email'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Model $record) => $record->id === auth()->id() ? Profile::getUrl() : UserResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->url(fn (Model $record) => $record->id === auth()->id() ? Profile::getUrl() : UserResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Model $record) {
                        if ($record->id === auth()->id()) {
                            return Notification::make()
                                ->title('Error!')
                                ->body('You cannot delete your own profile.')
                                ->icon('heroicon-o-shield-exclamation')
                                ->iconPosition(IconPosition::Before)
                                ->danger()
                                ->send();
                        }

                        return $record->delete();
                    })
                    ->requiresConfirmation(),
                Impersonate::make()
                    ->color('warning')
                    ->hidden(fn (Model $record) => ! $record->can('User.impersonate'))
                    ->link()
                    ->iconSize(IconSize::Small)
                    ->label('Impersonate')
                    ->icon('fas-user-secret'),
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
            RelationGroup::make('Authentication Logs', [
                AuthenticationLogsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id', '!=', auth()->id())
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
