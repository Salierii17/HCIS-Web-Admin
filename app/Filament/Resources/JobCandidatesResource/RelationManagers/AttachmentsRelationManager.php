<?php

namespace App\Filament\Resources\JobCandidatesResource\RelationManagers;

use App\Filament\Enums\AttachmentCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('attachment')
                    ->preserveFilenames()
                    ->storeFileNamesIn('attachmentName')
                    ->directory('JobOpening-attachments')
                    ->visibility('private')
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                    ])
                    ->disabled(fn ($record) => $record?->category === 'Resume') // Disable for resumes
                    ->required(),
                Forms\Components\TextInput::make('attachmentName')
                    ->disabledOn('edit')
                    ->hidden()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->options(AttachmentCategory::class)
                    ->required(),
                Forms\Components\TextInput::make('attachmentOwner')
                    ->readOnly()
                    ->default(fn ($livewire) => $livewire->ownerRecord->id)
                    ->required()
                    ->hidden()
                    ->maxLength(255),
                Forms\Components\TextInput::make('moduleName')
                    ->readOnly()
                    ->default('JobCandidates')
                    ->hidden()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attachmentName')
                    ->label('File Name'),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(function ($record) {
                        $filePath = str_replace('public/', '', $record->attachment);

                        return asset('storage/'.$filePath);
                    })
                    ->openUrlInNewTab()
                    ->hidden(fn ($record) => empty($record->attachment)),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        $path = storage_path('app/public/'.$record->attachment);

                        return response()->download($path, $record->attachmentName);
                    })
                    ->hidden(fn ($record) => empty($record->attachment)),
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
}
