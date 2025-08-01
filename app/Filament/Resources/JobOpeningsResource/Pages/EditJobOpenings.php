<?php

namespace App\Filament\Resources\JobOpeningsResource\Pages;

use App\Filament\Resources\JobOpeningsResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJobOpenings extends EditRecord
{
    protected static string $resource = JobOpeningsResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Format dates (though DateTimePicker should handle this)
        $data['TargetDate'] = Carbon::parse($data['TargetDate'])->format('Y-m-d H:i:s');
        $data['DateOpened'] = Carbon::parse($data['DateOpened'])->format('Y-m-d H:i:s');

        // Process rich text fields
        $richTextFields = ['JobDescription', 'JobRequirement', 'JobBenefits', 'AdditionalNotes'];
        foreach ($richTextFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->cleanRichText($data[$field]);
            }
        }

        return $data;
    }

    protected function cleanRichText(string $content): string
    {
        $content = preg_replace('/<p[^>]*>\s*<\/p>/', '', $content);
        $content = preg_replace('/<p[^>]*>/', '', $content);
        $content = str_replace('</p>', '<br><br>', $content);
        $content = preg_replace('/<[^\/>]*>([\s]?)*<\/[^>]*>/', '', $content);

        return trim($content);
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $data = $this->getRecord()->toArray();

        // Convert dates to proper format for form
        $data['TargetDate'] = $this->getRecord()->TargetDate?->format('Y-m-d H:i:s');
        $data['DateOpened'] = $this->getRecord()->DateOpened?->format('Y-m-d H:i:s');

        $this->form->fill($data);

        $this->callHook('afterFill');
    }
}
